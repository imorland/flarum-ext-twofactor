<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Tests\integration\console;

use Carbon\Carbon;
use Flarum\Http\AccessToken;
use Flarum\Testing\integration\ConsoleTestCase;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\User\User;
use Illuminate\Console\Scheduling\Schedule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class KillInactiveTokensCommandTest extends ConsoleTestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');

        $old = Carbon::now()->subDays(40)->toDateTimeString();
        $recent = Carbon::now()->subDays(5)->toDateTimeString();

        $this->prepareDatabase([
            User::class => [$this->normalUser()],
            AccessToken::class => [
                ['token' => 'oldsession', 'user_id' => 2, 'type' => 'session', 'created_at' => $old, 'last_activity_at' => $old],
                ['token' => 'oldremember', 'user_id' => 2, 'type' => 'session_remember', 'created_at' => $old, 'last_activity_at' => $old],
                ['token' => 'olddeveloper', 'user_id' => 2, 'type' => 'developer', 'created_at' => $old, 'last_activity_at' => $old],
                ['token' => 'unuseddeveloper', 'user_id' => 2, 'type' => 'developer', 'created_at' => $old, 'last_activity_at' => null],
                ['token' => 'newdeveloper', 'user_id' => 2, 'type' => 'developer', 'created_at' => $recent, 'last_activity_at' => null],
                ['token' => 'recentdeveloper', 'user_id' => 2, 'type' => 'developer', 'created_at' => $recent, 'last_activity_at' => $recent],
            ],
        ]);
    }

    #[Test]
    public function it_deletes_only_unused_developer_tokens()
    {
        $this->setting('ianm-twofactor.also_kill_developer_tokens', '1');

        $this->runCommand(['command' => 'twofactor:kill-inactive-tokens']);

        $this->assertEquals(['newdeveloper', 'oldremember', 'oldsession', 'recentdeveloper'], $this->tokens());
    }

    #[Test]
    public function it_deletes_nothing_while_turned_off()
    {
        $this->setting('ianm-twofactor.also_kill_developer_tokens', '0');

        $this->runCommand(['command' => 'twofactor:kill-inactive-tokens']);

        $this->assertCount(6, $this->tokens());
    }

    #[Test]
    #[DataProvider('unusableAges')]
    public function it_deletes_nothing_without_a_usable_age(string $age)
    {
        $this->setting('ianm-twofactor.also_kill_developer_tokens', '1');
        $this->setting('ianm-twofactor.kill_inactive_tokens_age_days', $age);

        $this->runCommand(['command' => 'twofactor:kill-inactive-tokens']);

        $this->assertCount(6, $this->tokens());
    }

    public static function unusableAges(): array
    {
        return ['empty' => [''], 'zero' => ['0'], 'negative' => ['-5'], 'not a number' => ['abc']];
    }

    #[Test]
    public function it_is_scheduled_twice_a_day_and_runs_only_while_turned_on()
    {
        $this->setting('ianm-twofactor.also_kill_developer_tokens', '0');

        $this->app();
        $schedule = $this->app()->getContainer()->make(Schedule::class);
        $event = collect($schedule->events())->first(fn ($event) => str_contains((string) $event->command, 'twofactor:kill-inactive-tokens'));

        $this->assertNotNull($event);
        $this->assertSame('0 1,13 * * *', $event->expression);
        $this->assertFalse($event->filtersPass($this->app()->getContainer()));
    }

    protected function tokens(): array
    {
        return AccessToken::query()->orderBy('token')->pluck('token')->all();
    }
}
