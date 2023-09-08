<?php

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
