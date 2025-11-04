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
use Flarum\Group\Group;

class AddGroupAttributes
{
    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('requires2FA')
                ->get(function (Group $group, Context $context): bool {
                    return $group->tfa_required;
                })
                ->visible(function (Group $group, Context $context): bool {
                    $actor = $context->getActor();

                    return $actor->can('ianm-twofactor.seeTwoFactorStatus');
                })
        ];
    }
}
