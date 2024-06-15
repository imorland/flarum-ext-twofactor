<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Console;

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Console\Scheduling\Event;

class InactiveTokensSchedule
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function __invoke(Event $event)
    {
        if (! (bool) $this->settings->get('ianm-twofactor.kill_inactive_tokens')) {
            return;
        }

        $event->twiceDaily();
    }
}
