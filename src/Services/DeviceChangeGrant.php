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

use Flarum\User\User;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Str;

/**
 * Proof that a user gave a code from their current device, which setting up a
 * new device requires. It goes to the client that gave the code, not to every
 * session of the user, and lasts as long as a pending new secret does.
 */
class DeviceChangeGrant
{
    protected const TTL_SECONDS = 600;

    public function __construct(
        protected Repository $cache
    ) {
    }

    public function issue(User $user): string
    {
        $token = Str::random(40);

        $this->cache->put($this->key($user), hash('sha256', $token), self::TTL_SECONDS);

        return $token;
    }

    public function check(User $user, mixed $token): bool
    {
        $hash = $this->cache->get($this->key($user));

        return is_string($token) && $token !== '' && is_string($hash) && hash_equals($hash, hash('sha256', $token));
    }

    public function revoke(User $user): void
    {
        $this->cache->forget($this->key($user));
    }

    protected function key(User $user): string
    {
        return 'twofactor-device-change:'.$user->id;
    }
}
