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

use Carbon\Carbon;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use IanM\TwoFactor\Model\TwoFactor;

class ShowQrCodeControllerTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'normal2', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'normal2@machine.local', 'is_email_confirmed' => 1,
                ]
            ],
            'two_factor' => [
                ['id' => 1, 'user_id' => 2, 'secret' => 'OIZ2R42HL2ZNUJNJU72P4EK26CQSD5JLEC7AVH7BCBJKRCUBUPLHXQ4TCAYVFZPDAGH3QDPHWABLMT36QAKTIFPNL5NKTR2BGVIY3GY', 'backup_codes' => '["$2y$10$8UDXx3Fbx\/K9uKHs.4wq8OIP3\/q.0PghYhX\/v9ckHmvXwY2yUI.IC","$2y$10$KWw6OT18AMWa\/T1NcS1hjOiMfuzq45L1KKsFUBXAIjKTsvXJcUEOW"]', 'is_active' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ]
        ]);
    }

    /**
     * @test
     */
    public function user_can_generate_qr_code()
    {
        $response = $this->send(
            $this->request('GET', '/api/users/3/twofactor/qrcode', [
                'authenticatedAs' => 3,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertJson($body);

        $data = json_decode($body, true);
        $this->assertArrayHasKey('svg', $data);
        $this->assertArrayHasKey('code', $data);

        $this->assertStringContainsString('data:image/png;base64,', $data['svg']);
        $this->assertNotEmpty($data['code']);

        $twoFactor = TwoFactor::query()->where('user_id', 3)->first();

        $this->assertNotNull($twoFactor);
        $this->assertEquals($data['code'], $twoFactor->secret);
        $this->assertFalse($twoFactor->is_active);
    }

    /**
     * @test
     */
    public function unauthenticated_user_cannot_generate_qr_code()
    {
        $response = $this->send(
            $this->request('GET', '/api/users/3/twofactor/qrcode')
        );

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function user_cannot_generate_qr_code_for_another_user()
    {
        // User 3 is authenticated, but they try to hit the endpoint for user 2 (different user)
        $response = $this->send(
            $this->request('GET', '/api/users/2/twofactor/qrcode', [
                'authenticatedAs' => 3,
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function generating_qr_code_updates_two_factor_record()
    {
        $response = $this->send(
            $this->request('GET', '/api/users/3/twofactor/qrcode', [
                'authenticatedAs' => 3,
            ])
        );
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);

        $twoFactor = TwoFactor::query()->where('user_id', 3)->first();
        $this->assertNotNull($twoFactor);
        $this->assertEquals($data['code'], $twoFactor->secret);
        $this->assertFalse($twoFactor->is_active);
    }
}
