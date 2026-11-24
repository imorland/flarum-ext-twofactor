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

use Carbon\Carbon;
use Flarum\Http\DeveloperAccessToken;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Console\Command;

class KillInactiveTokensCommand extends Command
{
    protected $signature = 'twofactor:kill-inactive-tokens';
    protected $description = 'Delete developer tokens that have not been used for the configured number of days';

    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
        parent::__construct();
    }

    /**
     * Core expires every other token type itself, with lifetimes set on the
     * Advanced page. Developer tokens are left to never expire there on purpose.
     */
    public function handle(): void
    {
        if (! (bool) $this->settings->get('ianm-twofactor.also_kill_developer_tokens')) {
            $this->info('Deleting unused developer tokens is turned off.');

            return;
        }

        $age = (int) $this->settings->get('ianm-twofactor.kill_inactive_tokens_age_days');

        $count = DeveloperAccessToken::query()
            ->where('type', DeveloperAccessToken::$type)
            ->where('last_activity_at', '<', Carbon::now()->subDays($age))
            ->delete();

        $this->info("Deleted $count developer tokens which have not been used in $age+ days.");
    }
}
