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
use Flarum\Extend;
use Flarum\Http\AccessToken;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;

class LoginTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extend(
            (new Extend\Csrf)->exemptRoute('login')
        );

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
    public function it_does_not_require_2fa_token_if_2fa_is_not_enabled()
    {
        $response = $this->send(
            $this->request('POST', '/login', [
                'json' => [
                    'identification' => 'normal2',
                    'password' => 'too-obscure',
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        // The response body should contain the user ID...
        $body = (string) $response->getBody();
        $this->assertJson($body);

        $data = json_decode($body, true);
        $this->assertEquals(3, $data['userId']);

        // ...and an access token belonging to this user.
        $token = $data['token'];
        $this->assertEquals(3, AccessToken::whereToken($token)->firstOrFail()->user_id);
    }

    /**
     * @test
     */
    public function it_requires_2fa_token_if_2fa_is_enabled()
    {
        $response = $this->send(
            $this->request('POST', '/login', [
                'json' => [
                    'identification' => 'normal',
                    'password' => 'too-obscure',
                ],
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());

        $body = (string) $response->getBody();
        $this->assertJson($body);

        $data = json_decode($body, true);
        $this->assertEquals('validation_error', $data['errors'][0]['code']);
        $this->assertEquals('two_factor_required', $data['errors'][0]['detail']);
        $this->assertEquals('/data/attributes/twoFactorToken', $data['errors'][0]['source']['pointer']);
    }

    // TODO: Add tests for 2FA token validation, backup codes, etc
}
