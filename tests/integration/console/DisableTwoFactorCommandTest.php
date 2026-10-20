<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\tests\integration\console;

use Carbon\Carbon;
use Flarum\Testing\integration\ConsoleTestCase;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class DisableTwoFactorCommandTest extends ConsoleTestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'normal2', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'normal2@machine.local', 'is_email_confirmed' => 1],
            ],
            TwoFactor::class => [
                ['id' => 1, 'user_id' => 2, 'secret' => 'OIZ2R42HL2ZNUJNJU72P4EK26CQSD5JLEC7AVH7BCBJKRCUBUPLHXQ4TCAYVFZPDAGH3QDPHWABLMT36QAKTIFPNL5NKTR2BGVIY3GY', 'backup_codes' => '[]', 'is_active' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ],
        ]);
    }

    public static function identifications(): array
    {
        return [
            'username' => ['normal'],
            'email' => ['normal@machine.local'],
            'id' => ['2'],
        ];
    }

    #[Test]
    #[DataProvider('identifications')]
    public function it_disables_2fa_for_a_user(string $identification)
    {
        $output = $this->runCommand([
            'command' => 'twofactor:disable',
            'user' => $identification,
            '--force' => true,
        ]);

        $this->assertStringContainsString('Two-factor authentication disabled for normal', $output);
        $this->assertFalse($this->twoFactorActive(2));
    }

    #[Test]
    public function it_does_nothing_when_not_confirmed()
    {
        $output = $this->runCommand([
            'command' => 'twofactor:disable',
            'user' => 'normal',
            '--no-interaction' => true,
        ]);

        $this->assertStringContainsString('Aborted', $output);
        $this->assertTrue($this->twoFactorActive(2));
    }

    #[Test]
    public function it_reports_an_unknown_user()
    {
        $output = $this->runCommand([
            'command' => 'twofactor:disable',
            'user' => 'nobody',
            '--force' => true,
        ]);

        $this->assertStringContainsString('No user found matching "nobody"', $output);
        $this->assertTrue($this->twoFactorActive(2));
    }

    #[Test]
    public function it_reports_a_user_without_2fa()
    {
        $output = $this->runCommand([
            'command' => 'twofactor:disable',
            'user' => 'normal2',
            '--force' => true,
        ]);

        $this->assertStringContainsString('normal2 does not have two-factor authentication enabled', $output);
    }

    private function twoFactorActive(int $userId): bool
    {
        return TwoFactor::query()->where('user_id', $userId)->where('is_active', true)->exists();
    }
}
