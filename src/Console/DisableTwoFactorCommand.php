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

use Flarum\User\User;
use Flarum\User\UserRepository;
use IanM\TwoFactor\Event\Disabled;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Recovery path for a user, typically an admin, who has lost both their
 * authentication device and their backup codes.
 */
class DisableTwoFactorCommand extends Command
{
    use TwoFactorAuthenticationTrait;

    protected $signature = 'twofactor:disable
        {user : Username, email address or ID of the user}
        {--force : Disable without asking for confirmation}';

    protected $description = 'Disable two-factor authentication for a user';

    public function __construct(
        protected UserRepository $users,
        protected Dispatcher $events
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $identification = (string) $this->argument('user');

        $user = ctype_digit($identification)
            ? User::find((int) $identification)
            : $this->users->findByIdentification($identification);

        if (! $user) {
            $this->error("No user found matching \"$identification\".");

            return self::FAILURE;
        }

        if (! $this->twoFactorActive($user)) {
            $this->info("$user->username does not have two-factor authentication enabled.");

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Disable two-factor authentication for $user->username?")) {
            $this->info('Aborted.');

            return self::FAILURE;
        }

        $user->twoFactor->delete();

        $this->events->dispatch(new Disabled($user, null));

        $this->info("Two-factor authentication disabled for $user->username.");

        return self::SUCCESS;
    }
}
