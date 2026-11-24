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
use Psr\Http\Message\ServerRequestInterface;

/**
 * Counting per user, from any address, is safe here, unlike core's login
 * throttle: only someone who already holds the user's password, session, reset
 * link or first OAuth factor can submit a code for them.
 */
class CodeVerificationThrottler
{
    public function __construct(
        protected RateLimiter $limiter
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

        $userAttempts = $this->limiter->hit($userKey, LoginThrottler::$decaySeconds);
        $ipAttempts = $this->limiter->hit($ipKey, LoginThrottler::$ipDecaySeconds);

        if ($userAttempts > LoginThrottler::$maxAttempts || $ipAttempts > LoginThrottler::$maxAttemptsPerIp) {
            $this->forget($userKey, $ipKey);

            return false;
        }

        return true;
    }

    /**
     * Uncounts an attempt whose code was correct.
     */
    public function succeeded(User $user, ServerRequestInterface $request): void
    {
        if (! $request->getAttribute('bypassThrottling')) {
            $this->forget(...$this->keys($user, $request));
        }
    }

    protected function forget(string $userKey, string $ipKey): void
    {
        $this->limiter->decrement($userKey, LoginThrottler::$decaySeconds);
        $this->limiter->decrement($ipKey, LoginThrottler::$ipDecaySeconds);
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
