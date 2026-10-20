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

use Flarum\Group\Event\Saving;
use IanM\TwoFactor\Event\GroupRequirementChanged;
use IanM\TwoFactor\Model\TwoFactor;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;

class SaveGroup2FASetting
{
    private string $arrayKey = 'attributes.requires2FA';

    public function __construct(protected Dispatcher $events)
    {
    }

    public function __invoke(Saving $event): void
    {
        if (Arr::has($event->data, $this->arrayKey) && ! $this->isGuardedGroup($event)) {
            $event->group->tfa_required = (bool) Arr::get($event->data, $this->arrayKey);
        }

        if ($event->group->exists && $event->group->isDirty('tfa_required')) {
            $event->group->afterSave(function () use ($event) {
                $this->events->dispatch(new GroupRequirementChanged($event->group, $event->actor, (bool) $event->group->tfa_required));
            });
        }
    }

    private function isGuardedGroup(Saving $event): bool
    {
        if (in_array($event->group->id, TwoFactor::guardedGroups())) {
            return true;
        }

        return false;
    }
}
