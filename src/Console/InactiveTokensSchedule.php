<?php

namespace IanM\TwoFactor\Console;

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Console\Scheduling\Event;

class InactiveTokensSchedule
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) { }

    public function __invoke(Event $event)
    {
        if (! (bool) $this->settings->get('ianm-twofactor.kill_inactive_tokens')) {
            return;
        }
        
        $event->twiceDaily();
    }
}
