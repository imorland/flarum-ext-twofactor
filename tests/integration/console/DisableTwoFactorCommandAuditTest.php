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
use Flarum\Audit\AuditLog;
use Flarum\Audit\AuditLogger;
use Flarum\Testing\integration\ConsoleTestCase;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use PHPUnit\Framework\Attributes\Test;

class DisableTwoFactorCommandAuditTest extends ConsoleTestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        // Audit's request middleware sets these statics; a real console process never has them.
        // Reset them so a previous HTTP test in this PHPUnit process can't leak its actor or IP.
        AuditLogger::$testMode = true;
        AuditLogger::$actor = null;
        AuditLogger::$ipAddress = null;
        AuditLogger::$client = null;

        $this->extension('flarum-audit', 'ianm-twofactor');

        $this->prepareDatabase([
            'audit_log' => [],
            User::class => [
                $this->normalUser(),
            ],
            TwoFactor::class => [
                ['id' => 1, 'user_id' => 2, 'secret' => 'OIZ2R42HL2ZNUJNJU72P4EK26CQSD5JLEC7AVH7BCBJKRCUBUPLHXQ4TCAYVFZPDAGH3QDPHWABLMT36QAKTIFPNL5NKTR2BGVIY3GY', 'backup_codes' => '[]', 'is_active' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ],
        ]);
    }

    #[Test]
    public function disabling_2fa_from_the_console_is_logged_as_a_system_action()
    {
        $output = $this->runCommand([
            'command' => 'twofactor:disable',
            'user' => 'normal',
            '--force' => true,
        ]);

        $this->assertStringContainsString('Two-factor authentication disabled for normal', $output);

        $log = AuditLog::query()->where('action', 'user.two_factor_disabled')->first();

        $this->assertNotNull($log, 'Asserting user.two_factor_disabled was logged');
        $this->assertNull($log->actor_id, 'Asserting no actor');
        $this->assertEquals(['user_id' => 2], $log->payload);
        $this->assertEquals('cli', $log->client);
        $this->assertNull($log->ip_address);
    }
}
