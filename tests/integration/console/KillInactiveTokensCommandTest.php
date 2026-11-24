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
                ['token' => 'recentdeveloper', 'user_id' => 2, 'type' => 'developer', 'created_at' => $recent, 'last_activity_at' => $recent],
            ],
        ]);
    }

    #[Test]
    public function it_deletes_only_unused_developer_tokens()
    {
        $this->setting('ianm-twofactor.also_kill_developer_tokens', '1');

        $this->runCommand(['command' => 'twofactor:kill-inactive-tokens']);

        $this->assertEquals(['oldremember', 'oldsession', 'recentdeveloper'], $this->tokens());
    }

    #[Test]
    public function it_deletes_nothing_while_turned_off()
    {
        $this->setting('ianm-twofactor.also_kill_developer_tokens', '0');

        $this->runCommand(['command' => 'twofactor:kill-inactive-tokens']);

        $this->assertCount(4, $this->tokens());
    }

    protected function tokens(): array
    {
        return AccessToken::query()->orderBy('token')->pluck('token')->all();
    }
}
