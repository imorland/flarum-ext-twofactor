<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Tests\Integration\OAuth\Stubs;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;

class FakeAccessToken implements AccessTokenInterface
{
    public function getToken(): string { return 'fake-token'; }
    public function getRefreshToken(): ?string { return null; }
    public function getExpires(): ?int { return null; }
    public function hasExpired(): bool { return false; }
    public function getValues(): array { return []; }
    public function __toString(): string { return 'fake-token'; }
    public function jsonSerialize(): mixed { return ['access_token' => 'fake-token']; }
}

class FakeResourceOwner implements ResourceOwnerInterface
{
    public function __construct(private string $id = 'fake-id') {}
    public function getId(): string { return $this->id; }
    public function toArray(): array { return ['id' => $this->id]; }
}

namespace IanM\TwoFactor\Tests\Integration\OAuth;

use Carbon\Carbon;
use Flarum\Extend;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\LoginProvider;
use Flarum\User\User;
use FoF\OAuth\Controllers\AbstractOAuthController;
use FoF\OAuth\Events\OAuthLoginSuccessful;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\OAuth\TwoFactorOAuthCheck;
use IanM\TwoFactor\OAuth\TwoFactorOAuthListener;
use Illuminate\Contracts\Cache\Store as CacheStore;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Session\Store as SessionStore;
use Laminas\Diactoros\ServerRequest;
use IanM\TwoFactor\Tests\Integration\OAuth\Stubs\FakeAccessToken;
use IanM\TwoFactor\Tests\Integration\OAuth\Stubs\FakeResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use PHPUnit\Framework\Attributes\Test;

class OAuthTwoFactorTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-oauth', 'ianm-twofactor');

        $this->extend(
            (new Extend\Csrf)
                ->exemptRoute('twoFactor.oauth.verify')
        );

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            TwoFactor::class => [
                [
                    'id' => 1,
                    'user_id' => 2,
                    'secret' => 'OIZ2R42HL2ZNUJNJU72P4EK26CQSD5JLEC7AVH7BCBJKRCUBUPLHXQ4TCAYVFZPDAGH3QDPHWABLMT36QAKTIFPNL5NKTR2BGVIY3GY',
                    'backup_codes' => '[]',
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ],
            LoginProvider::class => [
                [
                    'id' => 1,
                    'user_id' => 2,
                    'provider' => 'github',
                    'identifier' => 'gh-user-123',
                    'created_at' => Carbon::now(),
                    'last_login_at' => Carbon::now(),
                ],
            ],
        ]);
    }

    private function makeOAuthEvent(string $provider = 'github', string $identifier = 'gh-user-123'): OAuthLoginSuccessful
    {
        return new OAuthLoginSuccessful(
            token: new FakeAccessToken(),
            userResource: new FakeResourceOwner($identifier),
            providerName: $provider,
            identifier: $identifier,
        );
    }

    /**
     * Create a real session store via the session manager and bind a fake PSR-7
     * request to 'fof-oauth-request' so TwoFactorOAuthListener can find it.
     */
    private function makeSessionAndBindRequest(): SessionStore
    {
        /** @var SessionStore $session */
        $session = $this->app()->getContainer()->make('session')->driver();
        $session->setId(null); // generate a fresh ID

        $request = (new ServerRequest([], [], '/', 'GET'))
            ->withAttribute('session', $session);

        $this->app()->getContainer()->instance('fof-oauth-request', $request);

        return $session;
    }

    // -------------------------------------------------------------------------
    // TwoFactorOAuthListener tests
    // -------------------------------------------------------------------------

    #[Test]
    public function listener_does_nothing_for_user_without_2fa(): void
    {
        $this->prepareDatabase([
            User::class => [
                ['id' => 3, 'username' => 'no2fa', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'no2fa@example.com', 'is_email_confirmed' => 1],
            ],
            LoginProvider::class => [
                ['id' => 2, 'user_id' => 3, 'provider' => 'github', 'identifier' => 'gh-no2fa', 'created_at' => Carbon::now(), 'last_login_at' => Carbon::now()],
            ],
        ]);

        $session = $this->makeSessionAndBindRequest();

        $events = $this->app()->getContainer()->make(Dispatcher::class);
        $events->dispatch($this->makeOAuthEvent('github', 'gh-no2fa'));

        $this->assertNull($session->get(TwoFactorOAuthListener::SESSION_KEY_INTERCEPT));

        $cache = $this->app()->getContainer()->make(CacheStore::class);
        $this->assertNull($cache->get('oauth_data_'.$session->getId()));
    }

    #[Test]
    public function listener_sets_intercept_and_caches_oauth_data_for_user_with_2fa(): void
    {
        $session = $this->makeSessionAndBindRequest();

        $events = $this->app()->getContainer()->make(Dispatcher::class);
        $events->dispatch($this->makeOAuthEvent('github', 'gh-user-123'));

        $this->assertEquals('github', $session->get(TwoFactorOAuthListener::SESSION_KEY_INTERCEPT));

        $cache = $this->app()->getContainer()->make(CacheStore::class);
        $oauthData = $cache->get('oauth_data_'.$session->getId());

        $this->assertNotNull($oauthData);
        $this->assertEquals('github', $oauthData['provider']);
        $this->assertEquals(2, $oauthData['userId']);
        $this->assertInstanceOf(AccessTokenInterface::class, $oauthData['token']);
        $this->assertInstanceOf(ResourceOwnerInterface::class, $oauthData['resourceOwner']);
    }

    #[Test]
    public function listener_does_not_intercept_when_twofa_cleared_flag_is_set(): void
    {
        $session = $this->makeSessionAndBindRequest();
        $session->put(TwoFactorOAuthCheck::CACHE_KEY_CLEARED, true);

        $events = $this->app()->getContainer()->make(Dispatcher::class);
        $events->dispatch($this->makeOAuthEvent('github', 'gh-user-123'));

        // Flag should have been consumed
        $this->assertNull($session->get(TwoFactorOAuthCheck::CACHE_KEY_CLEARED));
        // Intercept should NOT have been set
        $this->assertNull($session->get(TwoFactorOAuthListener::SESSION_KEY_INTERCEPT));
    }

    #[Test]
    public function listener_does_nothing_for_unknown_provider_identifier(): void
    {
        $session = $this->makeSessionAndBindRequest();

        $events = $this->app()->getContainer()->make(Dispatcher::class);
        $events->dispatch($this->makeOAuthEvent('github', 'nobody'));

        $this->assertNull($session->get(TwoFactorOAuthListener::SESSION_KEY_INTERCEPT));
    }

    // -------------------------------------------------------------------------
    // TwoFactorOAuthVerifyController tests
    // -------------------------------------------------------------------------

    #[Test]
    public function verify_controller_redirects_back_when_token_is_missing(): void
    {
        $response = $this->send(
            $this->request('POST', '/twofactor/oauth/verify', [
                'json' => [],
            ])
        );

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('twofactor/oauth/verify', $response->getHeaderLine('Location'));
    }

    #[Test]
    public function verify_controller_redirects_back_when_oauth_session_has_expired(): void
    {
        $response = $this->send(
            $this->request('POST', '/twofactor/oauth/verify', [
                'json' => ['twoFactorToken' => '123456'],
            ])
        );

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('twofactor/oauth/verify', $response->getHeaderLine('Location'));
    }

    #[Test]
    public function verify_controller_redirects_back_on_invalid_totp(): void
    {
        $getResponse = $this->send($this->request('GET', '/twofactor/oauth/verify'));
        $this->assertEquals(200, $getResponse->getStatusCode());

        $sessionId = $this->extractSessionId($getResponse);
        $this->assertNotNull($sessionId, 'Could not extract session ID from GET response.');

        $cache = $this->app()->getContainer()->make(CacheStore::class);
        $cache->put('oauth_data_'.$sessionId, [
            'token' => new FakeAccessToken(),
            'resourceOwner' => new FakeResourceOwner('gh-user-123'),
            'provider' => 'github',
            'userId' => 2,
        ], AbstractOAuthController::$OAUTH_DATA_CACHE_LIFETIME);

        $postResponse = $this->send(
            $this->request('POST', '/twofactor/oauth/verify', [
                'cookiesFrom' => $getResponse,
                'json' => ['twoFactorToken' => '000000'],
            ])
        );

        $this->assertEquals(302, $postResponse->getStatusCode());
        $this->assertStringContainsString('twofactor/oauth/verify', $postResponse->getHeaderLine('Location'));
    }

    private function extractSessionId(\Psr\Http\Message\ResponseInterface $response): ?string
    {
        foreach ($response->getHeader('Set-Cookie') as $cookie) {
            if (preg_match('/^flarum_session=([^;]+)/', $cookie, $matches)) {
                return urldecode($matches[1]);
            }
        }

        return null;
    }
}
