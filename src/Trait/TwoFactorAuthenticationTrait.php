<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Trait;

use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;

trait TwoFactorAuthenticationTrait
{
    protected function twoFactorActive(User &$user): bool
    {
        if ($user->isGuest()) {
            return false;
        }

        $twoFactor = $user->twoFactor ?? TwoFactor::getForUser($user);
        $active = $twoFactor->is_active;

        if ($active === null) {
            $active = false;
        }

        return $active;
    }

    protected function retrieveTwoFactorTokenFrom(?string $source): ?string
    {
        if (empty($source)) {
            return null;
        }

        return $source;
    }

    protected function isTokenActive(?string $token, User $user): bool
    {
        return $token && $this->totp->verify($user->twoFactor->secret, $token, $user);
    }
}
