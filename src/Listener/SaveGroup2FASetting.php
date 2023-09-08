<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Listener;

use Flarum\Foundation\ValidationException;
use Flarum\Group\Event\Saving;
use IanM\TwoFactor\Model\TwoFactor;
use Illuminate\Support\Arr;

class SaveGroup2FASetting
{
    private string $arrayKey = 'attributes.requires2FA';

    public function __invoke(Saving $event)
    {
        if (Arr::has($event->data, $this->arrayKey)) {
            $this->prevent2FAChangesForGuardedGroups($event);
            $event->group->tfa_required = (bool) Arr::get($event->data, $this->arrayKey);
        }
    }

    private function prevent2FAChangesForGuardedGroups($event): void
    {
        if (in_array($event->group->id, TwoFactor::guardedGroups())) {
            $state = (bool) Arr::get($event->data, $this->arrayKey) ? 'enabled' : 'disabled';

            throw new ValidationException(['message' => "The {$event->group->name_singular} group cannot have 2FA {$state}."]);
        }
    }
}
