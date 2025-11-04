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

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\User\User;
use IanM\TwoFactor\Services\BackupCodeGenerator;
use IanM\TwoFactor\Services\TwoFactorRestrictor;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;

class AddUserAttributes
{
    use TwoFactorAuthenticationTrait;

    public function __construct(
        protected TwoFactorRestrictor $restrictor,
        protected BackupCodeGenerator $backupCodeGenerator
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Relationship\ToOne::make('twoFactor')
                ->type('two-factor')
                ->includable(),

            Schema\Boolean::make('twoFactorEnabled')
                ->get(function (User $user): bool {
                    return $this->twoFactorActive($user);
                })
                ->visible(function (User $user, Context $context): bool {
                    return $context->getActor()->can('ianm-twofactor.seeTwoFactorStatus') || $user->id === $context->getActor()->id;
                }),

            // Check if the user can disable 2FA. We don't use a permission for this, so that we can restrict admin users too.
            Schema\Boolean::make('canDisable2FA')
                ->get(function (User $user): bool {
                    return $this->restrictor->canDisable2FA($user);
                })
                ->visible(function (User $user, Context $context): bool {
                    return $user->id === $context->getActor()->id || $context->getActor()->can('ianm-twofactor.manageOthers');
                }),

            Schema\Boolean::make('mustEnable2FA')
                ->get(function (User $user): bool {
                    return $this->restrictor->mustEnableTwoFactor($user);
                })
                ->visible(function (User $user, Context $context): bool {
                    return $user->id === $context->getActor()->id;
                }),

            Schema\Integer::make('backupCodesRemaining')
                ->get(function (User $user): int {
                    return $this->backupCodeGenerator->getRemainingBackupCodes($user);
                })
                ->visible(function (User $user, Context $context): bool {
                    return $context->getActor()->can('ianm-twofactor.seeTwoFactorStatus') || $user->id === $context->getActor()->id;
                }),
        ];
    }
}
