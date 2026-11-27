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
     * Core expires session and "remember me" tokens itself. It leaves developer
     * tokens to never expire on purpose.
     */
    public function handle(): void
    {
        if (! (bool) $this->settings->get('ianm-twofactor.also_kill_developer_tokens')) {
            $this->info('Deleting unused developer tokens is turned off.');

            return;
        }

        $age = (int) $this->settings->get('ianm-twofactor.kill_inactive_tokens_age_days');

        if ($age < 1) {
            $this->error('Set how many days a developer token may go unused before it is deleted.');

            return;
        }

        $cutoff = Carbon::now()->subDays($age);

        $count = DeveloperAccessToken::query()
            ->where('type', DeveloperAccessToken::$type)
            ->where(function ($query) use ($cutoff) {
                $query->where('last_activity_at', '<', $cutoff)
                    // Tokens created through the API start with no activity.
                    ->orWhere(fn ($query) => $query->whereNull('last_activity_at')->where('created_at', '<', $cutoff));
            })
            ->delete();

        $this->info("Deleted $count developer tokens which have not been used in $age+ days.");
    }
}
