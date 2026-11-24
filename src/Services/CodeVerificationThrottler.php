<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Services;

use Flarum\User\Throttler\LoginThrottler;
use Flarum\User\User;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Counting per user, from any address, is safe here, unlike core's login
 * throttle: only someone who already holds the user's password, session, reset
 * link or first OAuth factor can submit a code for them.
 */
class CodeVerificationThrottler
{
    /**
     * When each window that counted this request's attempt ends.
     *
     * @var array<string, mixed>
     */
    protected array $counted = [];

    public function __construct(
        protected RateLimiter $limiter,
        protected Repository $cache
    ) {
    }

    /**
     * Counts an attempt before the code is checked, so that concurrent requests
     * can't all pass the limit. Returns false, without counting, when over it.
     */
    public function attempt(User $user, ServerRequestInterface $request): bool
    {
        if ($request->getAttribute('bypassThrottling')) {
            return true;
        }

        [$userKey, $ipKey] = $this->keys($user, $request);

        try {
            return $this->locked([$userKey, $ipKey], function () use ($userKey, $ipKey) {
                $userAttempts = $this->limiter->hit($userKey, LoginThrottler::$decaySeconds);
                $ipAttempts = $this->limiter->hit($ipKey, LoginThrottler::$ipDecaySeconds);

                if ($userAttempts > LoginThrottler::$maxAttempts || $ipAttempts > LoginThrottler::$maxAttemptsPerIp) {
                    $this->limiter->decrement($userKey, LoginThrottler::$decaySeconds);
                    $this->limiter->decrement($ipKey, LoginThrottler::$ipDecaySeconds);

                    return false;
                }

                $this->counted = [$userKey => $this->windowEnd($userKey), $ipKey => $this->windowEnd($ipKey)];

                return true;
            });
        } catch (LockTimeoutException) {
            return false;
        }
    }

    public function succeeded(User $user, ServerRequestInterface $request): void
    {
        if ($request->getAttribute('bypassThrottling')) {
            return;
        }

        [$userKey, $ipKey] = $this->keys($user, $request);

        try {
            $this->locked([$userKey, $ipKey], function () use ($userKey, $ipKey) {
                $this->refund($userKey, LoginThrottler::$decaySeconds);
                $this->refund($ipKey, LoginThrottler::$ipDecaySeconds);
            });
        } catch (LockTimeoutException) {
            // Leaving the attempt counted only errs on the safe side.
        }
    }

    protected function refund(string $key, int $decaySeconds): void
    {
        // A window that has ended since took the attempt with it.
        if (isset($this->counted[$key]) && $this->windowEnd($key) === $this->counted[$key]) {
            $this->limiter->decrement($key, $decaySeconds);
        }

        unset($this->counted[$key]);
    }

    protected function windowEnd(string $key): mixed
    {
        return $this->cache->get("$key:timer");
    }

    /**
     * Some stores, such as the default file store, read and write a counter in
     * separate steps, so concurrent increments could otherwise be lost.
     *
     * @template T
     * @param string[] $keys
     * @param callable(): T $callback
     * @return T
     */
    protected function locked(array $keys, callable $callback): mixed
    {
        $store = $this->cache->getStore();

        if (! $store instanceof LockProvider) {
            return $callback();
        }

        $locks = [];

        try {
            // Always taken in the same order, so two requests can't wait on each other.
            foreach ($keys as $key) {
                $lock = $store->lock("$key:lock", 10);
                $lock->block(5);
                $locks[] = $lock;
            }

            return $callback();
        } finally {
            foreach (array_reverse($locks) as $lock) {
                $lock->release();
            }
        }
    }

    /**
     * @return array{string, string}
     */
    protected function keys(User $user, ServerRequestInterface $request): array
    {
        $ip = (string) $request->getAttribute('ipAddress');

        return [
            'twofactor-code-attempts:'.$user->id,
            'twofactor-code-attempts-ip:'.sha1($ip),
        ];
    }
}
