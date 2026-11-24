<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Tests\integration\api;

use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Tests\integration\GeneratesTwoFactorCodes;
use Illuminate\Contracts\Cache\Repository;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

class DeviceChangeTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use GeneratesTwoFactorCodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');

        $this->prepareDatabase([
            User::class => [$this->normalUser()],
            TwoFactor::class => [$this->twoFactorRow(2)],
        ]);
    }

    protected function tearDown(): void
    {
        $this->app()->getContainer()->make(Repository::class)->clear();

        parent::tearDown();
    }

    #[Test]
    public function the_device_can_be_changed_after_verifying_the_current_one()
    {
        $changeToken = $this->verifyCurrent($this->correctCode());
        $this->assertNotEmpty($changeToken);

        $qr = $this->qrCode($changeToken);
        $this->assertEquals(200, $qr->getStatusCode());
        $newSecret = json_decode((string) $qr->getBody(), true)['code'];

        $response = $this->verifyNew($this->correctCode($newSecret), $changeToken);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($newSecret, TwoFactor::query()->where('user_id', 2)->value('secret'));
    }

    #[Test]
    public function a_new_device_cannot_be_set_up_without_verifying_the_current_one()
    {
        $this->assertEquals(403, $this->qrCode(null)->getStatusCode());
        $this->assertEquals(403, $this->qrCode('not-the-token')->getStatusCode());
        $this->assertNull(TwoFactor::query()->where('user_id', 2)->value('temp_secret'));
    }

    #[Test]
    public function a_new_device_cannot_be_confirmed_without_verifying_the_current_one()
    {
        $changeToken = $this->verifyCurrent($this->correctCode());
        $newSecret = json_decode((string) $this->qrCode($changeToken)->getBody(), true)['code'];

        $this->assertEquals(403, $this->verifyNew($this->correctCode($newSecret), null)->getStatusCode());
        $this->assertEquals(403, $this->verifyNew($this->correctCode($newSecret), 'not-the-token')->getStatusCode());
        $this->assertEquals($this->secret, TwoFactor::query()->where('user_id', 2)->value('secret'));
    }

    #[Test]
    public function a_verification_is_used_up_by_the_change()
    {
        $changeToken = $this->verifyCurrent($this->correctCode());
        $newSecret = json_decode((string) $this->qrCode($changeToken)->getBody(), true)['code'];
        $this->verifyNew($this->correctCode($newSecret), $changeToken);

        $this->assertEquals(403, $this->qrCode($changeToken)->getStatusCode());
    }

    #[Test]
    public function someone_else_cannot_start_a_device_change()
    {
        $this->prepareDatabase([User::class => [['id' => 3, 'username' => 'other', 'email' => 'other@machine.local', 'is_email_confirmed' => 1]]]);

        $response = $this->send($this->request('POST', '/api/users/2/twofactor/change-device/qrcode', ['authenticatedAs' => 1]));

        $this->assertEquals(403, $response->getStatusCode());
    }

    #[Test]
    public function a_backup_code_does_not_confirm_a_new_device()
    {
        $this->app();
        TwoFactor::query()->where('user_id', 2)->update(['backup_codes' => json_encode([password_hash('246810', PASSWORD_BCRYPT)])]);

        $changeToken = $this->verifyCurrent($this->correctCode());
        $this->qrCode($changeToken);

        $this->assertEquals(422, $this->verifyNew('246810', $changeToken)->getStatusCode());
        $this->assertEquals($this->secret, TwoFactor::query()->where('user_id', 2)->value('secret'));
    }

    #[Test]
    public function turning_2fa_off_withdraws_a_pending_verification()
    {
        $changeToken = $this->verifyCurrent($this->correctCode());

        $this->send($this->request('DELETE', '/api/users/2/twofactor/disable', ['authenticatedAs' => 2, 'json' => ['token' => $this->correctCode()]]));
        $this->app();
        TwoFactor::query()->insert($this->twoFactorRow(2, 50, 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP'));

        $this->assertEquals(403, $this->qrCode($changeToken)->getStatusCode());
    }

    #[Test]
    public function a_verification_is_only_good_for_the_device_it_came_from()
    {
        $changeToken = $this->verifyCurrent($this->correctCode());

        // As twofactor:disable does, then enrolling again.
        $this->app();
        TwoFactor::query()->where('user_id', 2)->delete();
        TwoFactor::query()->insert($this->twoFactorRow(2, 50, 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP'));

        $this->assertEquals(403, $this->qrCode($changeToken)->getStatusCode());
    }

    #[Test]
    public function a_wrong_current_code_gives_no_verification()
    {
        $response = $this->send($this->request('POST', '/api/users/twofactor/verify-current', [
            'authenticatedAs' => 2,
            'json' => ['token' => $this->wrongCode()],
        ]));

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringNotContainsString('changeToken', (string) $response->getBody());
    }

    protected function verifyCurrent(string $code): ?string
    {
        $response = $this->send($this->request('POST', '/api/users/twofactor/verify-current', [
            'authenticatedAs' => 2,
            'json' => ['token' => $code],
        ]));

        $this->assertEquals(200, $response->getStatusCode());

        return json_decode((string) $response->getBody(), true)['changeToken'] ?? null;
    }

    protected function qrCode(?string $changeToken): ResponseInterface
    {
        return $this->send($this->request('POST', '/api/users/2/twofactor/change-device/qrcode', [
            'authenticatedAs' => 2,
            'json' => array_filter(['changeToken' => $changeToken]),
        ]));
    }

    protected function verifyNew(string $code, ?string $changeToken): ResponseInterface
    {
        return $this->send($this->request('POST', '/api/users/twofactor/change-device/verify', [
            'authenticatedAs' => 2,
            'json' => array_filter(['token' => $code, 'changeToken' => $changeToken]),
        ]));
    }
}
