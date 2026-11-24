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

use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Sites that relied on this extension to expire "remember me" tokens keep that
 * expiry, now through core's own token lifetime.
 */
class TokenLifetimeMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');
    }

    public static function cases(): array
    {
        // [old settings, core's remember lifetime before, after, developer cleanup after]
        return [
            'defaults' => [[], null, '2592000', null],
            'turned on, 10 days' => [['kill_inactive_tokens' => '1', 'kill_inactive_tokens_age_days' => '10'], null, '864000', null],
            'core already shorter' => [['kill_inactive_tokens' => '1'], '3600', '3600', null],
            'core longer' => [['kill_inactive_tokens' => '1'], '31536000', '2592000', null],
            'core never expires' => [['kill_inactive_tokens' => '1'], '0', '2592000', null],
            'turned off' => [['kill_inactive_tokens' => '0', 'also_kill_developer_tokens' => '1'], null, null, '0'],
            'turned on with developer tokens' => [['kill_inactive_tokens' => '1', 'also_kill_developer_tokens' => '1'], null, '2592000', '1'],
        ];
    }

    #[Test]
    #[DataProvider('cases')]
    public function it_moves_the_remember_token_expiry_to_core(array $old, ?string $before, ?string $after, ?string $developer)
    {
        $db = $this->database();
        $settings = $db->table('settings');

        $settings->whereIn('key', ['session.tokens.session_remember', 'ianm-twofactor.kill_inactive_tokens', 'ianm-twofactor.kill_inactive_tokens_age_days', 'ianm-twofactor.also_kill_developer_tokens'])->delete();

        foreach ($old as $key => $value) {
            $db->table('settings')->insert(['key' => "ianm-twofactor.$key", 'value' => $value]);
        }

        if ($before !== null) {
            $db->table('settings')->insert(['key' => 'session.tokens.session_remember', 'value' => $before]);
        }

        $migration = require __DIR__.'/../../../migrations/2026_11_24_000000_move_remember_token_expiry_to_core.php';
        $migration['up']($db->getSchemaBuilder());

        $value = fn (string $key) => $db->table('settings')->where('key', $key)->value('value');

        $this->assertSame($after, $value('session.tokens.session_remember'));
        $this->assertSame($developer, $value('ianm-twofactor.also_kill_developer_tokens'));
        $this->assertNull($value('ianm-twofactor.kill_inactive_tokens'));
    }
}
