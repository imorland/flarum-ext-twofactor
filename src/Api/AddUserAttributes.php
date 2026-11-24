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
                ->includable()
                // The status attributes below read the relation, so it is loaded in full.
                ->withoutRelatedScope()
                ->get(function (User $user, Context $context) {
                    return $this->canSeeStatus($user, $context) || $context->getActor()->can('ianm-twofactor.manageOthers') ? $user->twoFactor : null;
                }),

            Schema\Boolean::make('twoFactorEnabled')
                ->get(function (User $user): bool {
                    return $this->twoFactorActive($user);
                })
                ->visible(fn (User $user, Context $context): bool => $this->canSeeStatus($user, $context)),

            // Whether the actor can disable this user's 2FA. A group's 2FA requirement only stops users disabling their own.
            Schema\Boolean::make('canDisable2FA')
                ->get(function (User $user, Context $context): bool {
                    return $this->restrictor->canDisable2FAFor($context->getActor(), $user);
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
                ->visible(fn (User $user, Context $context): bool => $this->canSeeStatus($user, $context)),
        ];
    }

    protected function canSeeStatus(User $user, Context $context): bool
    {
        $actor = $context->getActor();

        return $user->id === $actor->id || $actor->can('ianm-twofactor.seeTwoFactorStatus');
    }
}
