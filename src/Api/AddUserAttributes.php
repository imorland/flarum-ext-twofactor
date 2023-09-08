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

use Flarum\Api\Serializer\BasicUserSerializer;
use Flarum\User\User;
use IanM\TwoFactor\Services\BackupCodeGenerator;
use IanM\TwoFactor\Services\TwoFactorRestrictor;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;

class AddUserAttributes
{
    use TwoFactorAuthenticationTrait;

    public function __construct(protected TwoFactorRestrictor $restrictor, protected BackupCodeGenerator $backupCodeGenerator)
    {
    }

    public function __invoke(BasicUserSerializer $serializer, User $user, array $attributes): array
    {
        // These attributes are added to all users, based on permission.
        $actor = $serializer->getActor();

        if ($actor->can('ianm-twofactor.seeTwoFactorStatus')) {
            $attributes['twoFactorEnabled'] = $this->twoFactorActive($user);
            $attributes['backupCodesRemaining'] = $this->backupCodeGenerator->getRemainingBackupCodes($user);
        }

        if ($actor->can('ianm-twofactor.manageOthers')) {
            $attributes['canDisable2FA'] = $this->restrictor->canDisable2FA($user);
        }

        return $attributes;
    }
}
