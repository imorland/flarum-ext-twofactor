<?php

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
