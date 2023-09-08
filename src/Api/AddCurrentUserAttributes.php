<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Api;

use Flarum\Api\Serializer\CurrentUserSerializer;
use Flarum\User\User;
use IanM\TwoFactor\Services\BackupCodeGenerator;
use IanM\TwoFactor\Services\TwoFactorRestrictor;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;

class AddCurrentUserAttributes
{
    use TwoFactorAuthenticationTrait;

    public function __construct(protected TwoFactorRestrictor $restrictor, protected BackupCodeGenerator $backupCodeGenerator)
    {
    }

    public function __invoke(CurrentUserSerializer $serializer, User $user, array $attributes): array
    {
        // These attributes are added only to the logged in user.

        // Check if the model exists and get the is_active attribute
        $attributes['twoFactorEnabled'] = $this->twoFactorActive($user);

        // Check if the user can disable 2FA. We don't use a permission for this, so that we can restrict admin users too.
        $attributes['canDisable2FA'] = $this->restrictor->canDisable2FA($user);

        // Check if the user must enable 2FA.
        $attributes['mustEnable2FA'] = $this->restrictor->mustEnableTwoFactor($user);

        $attributes['backupCodesRemaining'] = $this->backupCodeGenerator->getRemainingBackupCodes($user);

        return $attributes;
    }
}
