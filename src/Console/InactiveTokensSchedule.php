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

    public function __invoke(Event $event): void
    {
        // Without a frequency an event runs every minute, so set one even when off.
        $event->twiceDaily()
            ->when(fn () => (bool) $this->settings->get('ianm-twofactor.also_kill_developer_tokens'));
    }
}
