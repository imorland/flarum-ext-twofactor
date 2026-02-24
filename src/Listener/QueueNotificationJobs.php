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

use IanM\TwoFactor\Event\DeviceChanged;
use IanM\TwoFactor\Event\Disabled;
use IanM\TwoFactor\Event\Enabled;
use IanM\TwoFactor\Job;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Queue;

class QueueNotificationJobs
{
    public function __construct(
        protected Queue $queue
    ) {
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen([Enabled::class, Disabled::class, DeviceChanged::class], [$this, 'notify']);
    }

    public function notify(Enabled|Disabled|DeviceChanged $event): void
    {
        $this->queue->push(
            new Job\SendNotifications($event)
        );
    }
}
