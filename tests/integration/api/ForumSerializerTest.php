<?php

namespace IanM\TwoFactor\tests\integration\api;

use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;

class ForumSerializerTest extends TestCase
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
    public function it_includes_logo_url_in_forum_attributes_if_user_is_admin()
    {
        $response = $this->send(
            $this->request('GET', '/api', [
                'authenticatedAs' => 1,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('ianm_twofactor_logoUrl', $json['data']['attributes']);
    }

    /**
     * @test
     */
    public function it_does_not_include_logo_url_in_forum_attributes_if_user_is_not_admin()
    {
        $response = $this->send(
            $this->request('GET', '/api', [
                'authenticatedAs' => 2,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayNotHasKey('ianm_twofactor_logoUrl', $json['data']['attributes']);
    }

    /**
     * @test
     */
    public function it_does_not_include_logo_url_in_forum_attributes_if_user_is_not_authenticated()
    {
        $response = $this->send(
            $this->request('GET', '/api')
        );

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayNotHasKey('ianm_twofactor_logoUrl', $json['data']['attributes']);
    }
}
