<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Job;

use Flarum\Notification\NotificationSyncer;
use Flarum\Queue\AbstractJob;
use IanM\TwoFactor\Event\Disabled;
use IanM\TwoFactor\Event\Enabled;
use IanM\TwoFactor\Notification\TwoFactorStatusChangedBlueprint;

class SendNotifications extends AbstractJob
{
    public function __construct(public Enabled|Disabled $event)
    {
    }

    public function handle(NotificationSyncer $notifications): void
    {
        $user = $this->event->user;

        $notifications->sync(new TwoFactorStatusChangedBlueprint($this->event), [$user]);
    }
}
