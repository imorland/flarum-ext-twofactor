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

    public function tooManyAttempts(User $user, ServerRequestInterface $request): bool
    {
        if ($request->getAttribute('bypassThrottling')) {
            return false;
        }

        [$userKey, $ipKey] = $this->keys($user, $request);

        return $this->limiter->tooManyAttempts($userKey, LoginThrottler::$maxAttempts)
            || $this->limiter->tooManyAttempts($ipKey, LoginThrottler::$maxAttemptsPerIp);
    }

    public function recordFailure(User $user, ServerRequestInterface $request): void
    {
        if ($request->getAttribute('bypassThrottling')) {
            return;
        }

        [$userKey, $ipKey] = $this->keys($user, $request);

        $this->limiter->hit($userKey, LoginThrottler::$decaySeconds);
        $this->limiter->hit($ipKey, LoginThrottler::$ipDecaySeconds);
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
