<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Tests\integration\OAuth;

use Flarum\Extend;
use Flarum\Group\Group;
use Flarum\OAuthProvider\Testing\InteractsWithOAuth;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Tests\integration\GeneratesTwoFactorCodes;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

class OAuthProviderTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use GeneratesTwoFactorCodes;
    use InteractsWithOAuth;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extend(
            (new Extend\Csrf())->exemptRoute('login')
        );

        $this->extension('flarum-oauth-provider', 'ianm-twofactor');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            Group::class => [
                ['id' => 5, 'name_singular' => 'Staff', 'name_plural' => 'Staff', 'tfa_required' => false],
            ],
            'group_user' => [
                ['user_id' => 2, 'group_id' => 5],
            ],
            'oauth_provider_clients' => [
                $this->clientRow('test-client', false),
            ],
        ]);
    }

    #[Test]
    public function a_member_who_must_enable_2fa_may_not_connect_an_application()
    {
        $this->requireTwoFactor();

        $params = $this->redirectParams($this->authorizeAsNormal());

        $this->assertEquals('access_denied', $params['error'] ?? null);
        $this->assertArrayNotHasKey('code', $params);
    }

    #[Test]
    public function a_member_who_has_enabled_2fa_may_connect_an_application()
    {
        $this->prepareDatabase([TwoFactor::class => [$this->twoFactorRow(2)]]);
        $this->requireTwoFactor();

        $params = $this->redirectParams($this->authorizeAsNormal($this->correctCode()));

        $this->assertArrayHasKey('code', $params);
    }

    #[Test]
    public function a_member_of_groups_without_the_requirement_may_connect_an_application()
    {
        $params = $this->redirectParams($this->authorizeAsNormal());

        $this->assertArrayHasKey('code', $params);
    }

    #[Test]
    public function connected_applications_stop_working_once_2fa_is_required()
    {
        $tokens = $this->publicClientTokens('test-client');

        $this->assertEquals(200, $this->userInfo($tokens['access_token'])->getStatusCode());

        $this->requireTwoFactor();

        $this->assertEquals(401, $this->userInfo($tokens['access_token'])->getStatusCode());
        $this->assertNotEquals(200, $this->refresh('test-client', $tokens['refresh_token'])->getStatusCode());
    }

    protected function requireTwoFactor(): void
    {
        $this->app();

        Group::query()->whereKey(5)->update(['tfa_required' => true]);
    }

    protected function authorizeAsNormal(?string $code = null): ResponseInterface
    {
        $login = $this->send(
            $this->request('POST', '/login', [
                'json' => array_filter(['identification' => 'normal', 'password' => 'too-obscure', 'twoFactorToken' => $code]),
            ])
        );

        $this->assertEquals(200, $login->getStatusCode(), (string) $login->getBody());

        return $this->authorize([
            'client_id' => 'test-client',
            'response_type' => 'code',
            'redirect_uri' => 'https://testapp.example.com/callback',
            'scope' => 'openid',
            'state' => 'xyz',
            'code_challenge' => $this->challenge(),
            'code_challenge_method' => 'S256',
        ], $login);
    }

    protected function userInfo(string $accessToken): ResponseInterface
    {
        return $this->send(
            $this->request('GET', '/oauth/userinfo')->withHeader('Authorization', "Bearer $accessToken")
        );
    }
}
