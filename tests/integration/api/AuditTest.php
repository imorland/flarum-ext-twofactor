<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\tests\integration\api;

use Carbon\Carbon;
use Flarum\Audit\AuditLog;
use Flarum\Audit\AuditLogger;
use Flarum\Extend;
use Flarum\Group\Group;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use OTPHP\TOTP;
use PHPUnit\Framework\Attributes\Test;

class AuditTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const SECRET = 'OIZ2R42HL2ZNUJNJU72P4EK26CQSD5JLEC7AVH7BCBJKRCUBUPLHXQ4TCAYVFZPDAGH3QDPHWABLMT36QAKTIFPNL5NKTR2BGVIY3GY';
    private const NEW_SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';
    private const BACKUP_CODE = '12345678';

    public function setUp(): void
    {
        parent::setUp();

        AuditLogger::$testMode = true;

        $this->extend(
            (new Extend\Csrf)->exemptRoute('login')
        );

        $this->extension('flarum-audit', 'ianm-twofactor');

        $this->prepareDatabase([
            'audit_log' => [],
            User::class => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'normal2', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'normal2@machine.local', 'is_email_confirmed' => 1],
                ['id' => 4, 'username' => 'moderator', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'moderator@machine.local', 'is_email_confirmed' => 1],
            ],
            'group_user' => [
                ['user_id' => 4, 'group_id' => Group::MODERATOR_ID],
            ],
            'group_permission' => [
                ['permission' => 'ianm-twofactor.manageOthers', 'group_id' => Group::MODERATOR_ID],
            ],
            Group::class => [
                ['id' => 100, 'name_singular' => 'Tester', 'name_plural' => 'Testers', 'tfa_required' => false],
                ['id' => 101, 'name_singular' => 'Guardian', 'name_plural' => 'Guardians', 'tfa_required' => true],
            ],
            TwoFactor::class => [
                [
                    'id' => 1, 'user_id' => 2, 'secret' => self::SECRET, 'is_active' => true,
                    'backup_codes' => json_encode([password_hash(self::BACKUP_CODE, PASSWORD_BCRYPT), password_hash('87654321', PASSWORD_BCRYPT)]),
                    'temp_secret' => self::NEW_SECRET, 'temp_secret_created_at' => Carbon::now(),
                    'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
                ],
                ['id' => 2, 'user_id' => 3, 'secret' => self::SECRET, 'is_active' => false, 'backup_codes' => '[]', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ],
        ]);
    }

    #[Test]
    public function enabling_2fa_is_logged()
    {
        $response = $this->send(
            $this->request('POST', '/api/users/twofactor/verify', [
                'authenticatedAs' => 3,
                'json' => ['token' => TOTP::create(self::SECRET)->now()],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLogExists('user.two_factor_enabled', ['user_id' => 3], 3);
    }

    #[Test]
    public function disabling_own_2fa_is_logged()
    {
        $response = $this->send(
            $this->request('DELETE', '/api/users/2/twofactor/disable', ['authenticatedAs' => 2])
        );

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertLogExists('user.two_factor_disabled', ['user_id' => 2], 2);
    }

    #[Test]
    public function disabling_another_users_2fa_is_logged_against_the_moderator()
    {
        $response = $this->send(
            $this->request('DELETE', '/api/users/2/twofactor/disable', ['authenticatedAs' => 4])
        );

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertLogExists('user.two_factor_disabled', ['user_id' => 2], 4);
    }

    #[Test]
    public function changing_device_is_logged()
    {
        $response = $this->send(
            $this->request('POST', '/api/users/twofactor/change-device/verify', [
                'authenticatedAs' => 2,
                'json' => ['token' => TOTP::create(self::NEW_SECRET)->now()],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLogExists('user.two_factor_device_changed', ['user_id' => 2], 2);
    }

    #[Test]
    public function logging_in_with_a_backup_code_is_logged()
    {
        $response = $this->send(
            $this->request('POST', '/login', [
                'json' => [
                    'identification' => 'normal',
                    'password' => 'too-obscure',
                    'twoFactorToken' => self::BACKUP_CODE,
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLogExists('user.two_factor_backup_code_used', ['user_id' => 2, 'remaining' => 1], null);
    }

    #[Test]
    public function logging_in_with_a_totp_code_does_not_log_backup_code_use()
    {
        $response = $this->send(
            $this->request('POST', '/login', [
                'json' => [
                    'identification' => 'normal',
                    'password' => 'too-obscure',
                    'twoFactorToken' => TOTP::create(self::SECRET)->now(),
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLogDoesntExist('user.two_factor_backup_code_used');
    }

    #[Test]
    public function requiring_2fa_for_a_group_is_logged()
    {
        $this->patchGroup(100, 'Tester', 'Testers', true);

        $this->assertLogExists('group.two_factor_required', ['group_id' => 100, 'name' => 'Tester'], 1);
        $this->assertLogDoesntExist('group.two_factor_not_required');
    }

    #[Test]
    public function no_longer_requiring_2fa_for_a_group_is_logged()
    {
        $this->patchGroup(101, 'Guardian', 'Guardians', false);

        $this->assertLogExists('group.two_factor_not_required', ['group_id' => 101, 'name' => 'Guardian'], 1);
        $this->assertLogDoesntExist('group.two_factor_required');
    }

    #[Test]
    public function saving_a_group_without_changing_the_2fa_requirement_is_not_logged()
    {
        $this->patchGroup(101, 'Guardian', 'Guardians', true);

        $this->assertLogDoesntExist('group.two_factor_required');
        $this->assertLogDoesntExist('group.two_factor_not_required');
    }

    private function patchGroup(int $id, string $nameSingular, string $namePlural, bool $requires2FA): void
    {
        $response = $this->send(
            $this->request('PATCH', "/api/groups/$id", [
                'authenticatedAs' => 1,
                'json' => [
                    'data' => [
                        'type' => 'groups',
                        'id' => (string) $id,
                        'attributes' => [
                            'nameSingular' => $nameSingular,
                            'namePlural' => $namePlural,
                            'requires2FA' => $requires2FA,
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
    }

    private function assertLogExists(string $action, array $payload, ?int $actorId): void
    {
        $log = AuditLog::query()->where('action', $action)->first();

        $this->assertNotNull($log, "Asserting $action was logged");
        $this->assertEquals($actorId, $log->actor_id, 'Asserting logged actor');
        $this->assertEquals($payload, $log->payload, 'Asserting logged payload');
        $this->assertEquals('127.0.0.1', $log->ip_address, 'Asserting logged IP');
    }

    private function assertLogDoesntExist(string $action): void
    {
        $this->assertNull(AuditLog::query()->where('action', $action)->first(), "Asserting $action was not logged");
    }
}
