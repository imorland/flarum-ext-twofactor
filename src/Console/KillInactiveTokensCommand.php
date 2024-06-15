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
use Flarum\Http\AccessToken;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Console\Command;

class KillInactiveTokensCommand extends Command
{
    protected $signature = 'twofactor:kill-inactive-tokens';
    protected $description = 'Kill all inactive tokens';

    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $age = (int) $this->settings->get('ianm-twofactor.kill_inactive_tokens_age_days');
        $maxAge = Carbon::now()->subdays($age);

        $query = AccessToken::query()
            ->where('last_activity_at', '<', $maxAge);

        if (! (bool) $this->settings->get('ianm-twofactor.also_kill_developer_tokens')) {
            $this->info('Not deleting any developer tokens.');
            $query->where('type', '!=', 'developer');
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info("No tokens found which have not been used in $age+ days.");

            return;
        }

        $this->info("Found $count tokens which have not been used in $age+ days. Deleting...");

        $this->output->progressStart($count);

        $query->delete();

        $this->output->progressFinish();
    }
}
