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

use IanM\TwoFactor\Event\Disabled;
use IanM\TwoFactor\Event\Enabled;
use IanM\TwoFactor\Job;
use Illuminate\Contracts\Events\Dispatcher;

class QueueNotificationJobs
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen([Enabled::class, Disabled::class], [$this, 'notify']);
    }

    public function notify(Enabled|Disabled $event)
    {
        resolve('flarum.queue.connection')->push(
            new Job\SendNotifications($event)
        );
    }
}
