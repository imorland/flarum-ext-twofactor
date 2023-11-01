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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;

class CurrentUserSerializerTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
            ],
            'two_factor' => [
                ['id' => 1, 'user_id' => 1, 'secret' => 'abcdef123456', 'backup_codes' => '["$2y$10$8UDXx3Fbx\/K9uKHs.4wq8OIP3\/q.0PghYhX\/v9ckHmvXwY2yUI.IC","$2y$10$KWw6OT18AMWa\/T1NcS1hjOiMfuzq45L1KKsFUBXAIjKTsvXJcUEOW"]', 'is_active' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_includes_two_factor_properties_in_current_user_attributes()
    {
        $response = $this->send(
            $this->request('GET', '/api/users/2', [
                'authenticatedAs' => 2,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('twoFactorEnabled', $json['data']['attributes']);
        $this->assertArrayHasKey('canDisable2FA', $json['data']['attributes']);
        $this->assertArrayHasKey('mustEnable2FA', $json['data']['attributes']);
        $this->assertArrayHasKey('backupCodesRemaining', $json['data']['attributes']);
    }

    /**
     * @test
     */
    public function it_does_not_include_two_factor_properties_in_current_user_attributes_if_user_is_different()
    {
        $response = $this->send(
            $this->request('GET', '/api/users/1', [
                'authenticatedAs' => 2,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayNotHasKey('twoFactorEnabled', $json['data']['attributes']);
        $this->assertArrayNotHasKey('canDisable2FA', $json['data']['attributes']);
        $this->assertArrayNotHasKey('mustEnable2FA', $json['data']['attributes']);
        $this->assertArrayNotHasKey('backupCodesRemaining', $json['data']['attributes']);
    }

    /**
     * @test
     */
    public function two_factor_is_enabled_for_given_user_and_returns_correct_properties()
    {
        $response = $this->send(
            $this->request('GET', '/api/users/1', [
                'authenticatedAs' => 1,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);

        $this->assertTrue($json['data']['attributes']['twoFactorEnabled']);
        $this->assertFalse($json['data']['attributes']['canDisable2FA']);
        $this->assertFalse($json['data']['attributes']['mustEnable2FA']);
        $this->assertEquals(2, $json['data']['attributes']['backupCodesRemaining']);
    }
}
