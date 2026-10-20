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
use Flarum\Foundation\MaintenanceMode;
use Flarum\Http\AccessToken;
use Flarum\Locale\TranslatorInterface;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use OTPHP\TOTP;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

/**
 * The server-rendered login page at /maintenance/login, used by guests while
 * the forum is in low maintenance mode, posts a plain HTML form to /login.
 */
class MaintenanceLoginTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const SECRET = 'OIZ2R42HL2ZNUJNJU72P4EK26CQSD5JLEC7AVH7BCBJKRCUBUPLHXQ4TCAYVFZPDAGH3QDPHWABLMT36QAKTIFPNL5NKTR2BGVIY3GY';

    public function setUp(): void
    {
        parent::setUp();

        $this->extend(
            (new Extend\Csrf)->exemptRoute('login')
        );

        $this->extension('ianm-twofactor');

        $this->setting('maintenance_mode', MaintenanceMode::LOW);

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'normal2', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'email' => 'normal2@machine.local', 'is_email_confirmed' => 1],
            ],
            TwoFactor::class => [
                ['id' => 1, 'user_id' => 2, 'secret' => self::SECRET, 'backup_codes' => '[]', 'is_active' => true, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ],
        ]);
    }

    #[Test]
    public function login_page_has_a_2fa_token_field()
    {
        $response = $this->send(
            $this->htmlRequest('GET', '/maintenance/login')
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('name="twoFactorToken"', (string) $response->getBody());
    }

    #[Test]
    public function form_login_without_2fa_token_redirects_back_with_an_error()
    {
        $response = $this->postLoginForm([
            'identification' => 'normal',
            'password' => 'too-obscure',
        ]);

        $this->assertRedirectsToMaintenanceLogin($response);
        $this->assertFalse(AccessToken::where('user_id', 2)->exists());
        $this->assertStringContainsString(
            $this->trans('ianm-twofactor.views.log_in.two_factor_required_message'),
            $this->followRedirect($response)
        );
    }

    #[Test]
    public function form_login_with_incorrect_2fa_token_redirects_back_with_an_error()
    {
        $response = $this->postLoginForm([
            'identification' => 'normal',
            'password' => 'too-obscure',
            'twoFactorToken' => '000000',
        ]);

        $this->assertRedirectsToMaintenanceLogin($response);
        $this->assertFalse(AccessToken::where('user_id', 2)->exists());
        $this->assertStringContainsString(
            $this->trans('ianm-twofactor.views.log_in.two_factor_incorrect_message'),
            $this->followRedirect($response)
        );
    }

    #[Test]
    public function form_login_with_valid_2fa_token_logs_in()
    {
        $response = $this->postLoginForm([
            'identification' => 'normal',
            'password' => 'too-obscure',
            'twoFactorToken' => TOTP::create(self::SECRET)->now(),
        ]);

        $this->assertRedirectsToMaintenanceLogin($response);
        $this->assertTrue(AccessToken::where('user_id', 2)->exists());
    }

    #[Test]
    public function form_login_with_remember_sets_the_remember_cookie()
    {
        $response = $this->postLoginForm([
            'identification' => 'normal',
            'password' => 'too-obscure',
            'twoFactorToken' => TOTP::create(self::SECRET)->now(),
            'remember' => '1',
        ]);

        $this->assertRedirectsToMaintenanceLogin($response);
        $this->assertStringContainsString('flarum_remember=', implode("\n", $response->getHeader('Set-Cookie')));
    }

    #[Test]
    public function form_login_without_2fa_enabled_logs_in()
    {
        $response = $this->postLoginForm([
            'identification' => 'normal2',
            'password' => 'too-obscure',
        ]);

        $this->assertRedirectsToMaintenanceLogin($response);
        $this->assertTrue(AccessToken::where('user_id', 3)->exists());
    }

    #[Test]
    public function form_login_with_wrong_password_redirects_back_with_an_error()
    {
        $response = $this->postLoginForm([
            'identification' => 'normal',
            'password' => 'wrong-password',
        ]);

        $this->assertRedirectsToMaintenanceLogin($response);
        $this->assertStringContainsString(
            $this->trans('core.views.log_in.invalid_login_message'),
            $this->followRedirect($response)
        );
    }

    #[Test]
    public function json_login_without_2fa_token_still_returns_422()
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

        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals('two_factor_required', $data['errors'][0]['detail']);
    }

    private function trans(string $key): string
    {
        return $this->app()->getContainer()->make(TranslatorInterface::class)->trans($key);
    }

    private function htmlRequest(string $method, string $path, array $options = [])
    {
        return $this->request($method, $path, $options)
            ->withHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
    }

    private function postLoginForm(array $body): ResponseInterface
    {
        return $this->send(
            $this->htmlRequest('POST', '/login')->withParsedBody($body)
        );
    }

    private function assertRedirectsToMaintenanceLogin(ResponseInterface $response): void
    {
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringEndsWith('/maintenance/login', $response->getHeaderLine('Location'));
    }

    private function followRedirect(ResponseInterface $response): string
    {
        $page = $this->send(
            $this->htmlRequest('GET', '/maintenance/login', ['cookiesFrom' => $response])
        );

        $this->assertEquals(200, $page->getStatusCode());

        return (string) $page->getBody();
    }
}
