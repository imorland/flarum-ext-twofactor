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

use Flarum\Group\Group;
use Flarum\User\User;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;

class TwoFactorRestrictor
{
    use TwoFactorAuthenticationTrait;

    /**
     * Determines if the given user can disable 2FA.
     *
     * @param User $user
     * @return bool
     */
    public function canDisable2FA(User $user): bool
    {
        return ! $this->hasRestrictedGroup($user);
    }

    /**
     * Determines if the given user has a restricted group that cannot disable 2FA.
     *
     * @param User $user
     * @return bool
     */
    protected function hasRestrictedGroup(User $user): bool
    {
        // Query the `Group` model to get the IDs of any groups that should restrict 2FA, based on the `2fa_required` column.
        $restrictedGroups = Group::query()->where('tfa_required', true)->get()->pluck('id')->toArray();

        return $user->groups->whereIn('id', $restrictedGroups)->isNotEmpty();
    }

    /**
     * Determines if the given user should/must enable 2FA.
     *
     * @param User $user
     * @return bool
     */
    public function mustEnableTwoFactor(User $user): bool
    {
        return $this->hasRestrictedGroup($user) && ! $this->twoFactorActive($user);
    }
}
