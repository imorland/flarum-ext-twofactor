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

use Flarum\Api\Serializer\GroupSerializer;
use Flarum\Group\Group;

class AddGroupAttributes
{
    public function __invoke(GroupSerializer $serializer, Group $group, array $attributes): array
    {
        $actor = $serializer->getActor();

        if ($actor->can('ianm-twofactor.seeTwoFactorStatus')) {
            $attributes['requires2FA'] = $group->tfa_required;
        }

        return $attributes;
    }
}
