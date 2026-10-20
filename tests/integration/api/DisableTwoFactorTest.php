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
use Flarum\Group\Group;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use PHPUnit\Framework\Attributes\Test;

class DisableTwoFactorTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const SECRET = 'OIZ2R42HL2ZNUJNJU72P4EK26CQSD5JLEC7AVH7BCBJKRCUBUPLHXQ4TCAYVFZPDAGH3QDPHWABLMT36QAKTIFPNL5NKTR2BGVIY3GY';

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'guarded', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'guarded@machine.local', 'is_email_confirmed' => 1],
            ],
            Group::class => [
                ['id' => 100, 'name_singular' => 'Guardian', 'name_plural' => 'Guardians', 'tfa_required' => true],
            ],
            'group_user' => [
                ['user_id' => 3, 'group_id' => 100],
            ],
            TwoFactor::class => [
                ['id' => 1, 'user_id' => 2, 'secret' => self::SECRET, 'backup_codes' => '[]', 'is_active' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
                ['id' => 2, 'user_id' => 3, 'secret' => self::SECRET, 'backup_codes' => '[]', 'is_active' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ],
        ]);
    }

    #[Test]
    public function user_can_disable_their_own_2fa()
    {
        $response = $this->disable(2, 2);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertFalse($this->twoFactorActive(2));
    }

    #[Test]
    public function user_in_a_group_requiring_2fa_cannot_disable_their_own_2fa()
    {
        $response = $this->disable(3, 3);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertTrue($this->twoFactorActive(3));
    }

    #[Test]
    public function user_cannot_disable_another_users_2fa_without_permission()
    {
        $response = $this->disable(2, 3);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertTrue($this->twoFactorActive(3));
    }

    #[Test]
    public function admin_can_disable_another_users_2fa_although_their_own_group_requires_it()
    {
        $response = $this->disable(1, 2);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertFalse($this->twoFactorActive(2));
    }

    #[Test]
    public function admin_can_disable_2fa_for_a_user_whose_group_requires_it()
    {
        $response = $this->disable(1, 3);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertFalse($this->twoFactorActive(3));
    }

    #[Test]
    public function disabling_2fa_for_an_unknown_user_returns_404()
    {
        $response = $this->disable(1, 999);

        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function can_disable_attribute_is_false_for_a_user_whose_own_group_requires_2fa()
    {
        $this->assertFalse($this->canDisableAttribute(3, 3));
    }

    #[Test]
    public function can_disable_attribute_is_true_for_an_admin_viewing_a_user_whose_group_requires_2fa()
    {
        $this->assertTrue($this->canDisableAttribute(1, 3));
    }

    private function disable(int $actorId, int $userId)
    {
        return $this->send(
            $this->request('DELETE', "/api/users/$userId/twofactor/disable", ['authenticatedAs' => $actorId])
        );
    }

    private function canDisableAttribute(int $actorId, int $userId): bool
    {
        $response = $this->send(
            $this->request('GET', "/api/users/$userId", ['authenticatedAs' => $actorId])
        );

        $this->assertEquals(200, $response->getStatusCode());

        return json_decode((string) $response->getBody(), true)['data']['attributes']['canDisable2FA'];
    }

    private function twoFactorActive(int $userId): bool
    {
        return TwoFactor::query()->where('user_id', $userId)->where('is_active', true)->exists();
    }
}
