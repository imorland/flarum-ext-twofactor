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
}
