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
use Flarum\Extend;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\PasswordToken;
use Flarum\User\Throttler\LoginThrottler;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Services\CodeVerificationThrottler;
use IanM\TwoFactor\Services\DeviceChangeGrant;
use IanM\TwoFactor\Tests\integration\GeneratesTwoFactorCodes;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

class CodeVerificationThrottleTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use GeneratesTwoFactorCodes;

    protected const NEW_SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';

    protected function setUp(): void
    {
        parent::setUp();

        $this->extend(
            (new Extend\Csrf())
                ->exemptRoute('savePassword')
                ->exemptRoute('user.twofactor.verify')
                ->exemptRoute('user.twofactor.verify-current')
                ->exemptRoute('user.twofactor.change-device.verify')
        );

        $this->extension('ianm-twofactor');

        $users = [$this->normalUser()];
        $twoFactors = [];

        foreach (range(2, 8) as $id) {
            if ($id > 2) {
                $users[] = ['id' => $id, 'username' => "user$id", 'email' => "user$id@machine.local", 'is_email_confirmed' => 1, 'password' => $this->normalUser()['password']];
            }

            $twoFactors[] = $this->twoFactorRow($id) + ['temp_secret' => self::NEW_SECRET, 'temp_secret_created_at' => Carbon::now()];
        }

        $this->prepareDatabase([
            User::class => $users,
            TwoFactor::class => $twoFactors,
            PasswordToken::class => [
                ['token' => 'resettoken', 'user_id' => 2, 'created_at' => Carbon::now()],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->app()->getContainer()->make(Repository::class)->clear();

        LoginThrottler::$decaySeconds = 60;
        Carbon::setTestNow();

        parent::tearDown();
    }

    public static function apiEndpoints(): array
    {
        return [
            'verify current device' => ['/api/users/twofactor/verify-current', false],
            'verify new device' => ['/api/users/twofactor/change-device/verify', true],
            're-verify' => ['/api/users/twofactor/verify', false],
        ];
    }

    #[Test]
    #[DataProvider('apiEndpoints')]
    public function wrong_codes_are_limited_per_user(string $path, bool $newDevice)
    {
        $this->failApiCodes($path, $newDevice, 5, 2);

        $response = $this->sendApiCode($path, $this->code($newDevice, false), 2);

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals('too_many_requests', json_decode((string) $response->getBody(), true)['errors'][0]['code']);
    }

    #[Test]
    #[DataProvider('apiEndpoints')]
    public function the_right_code_is_refused_while_limited(string $path, bool $newDevice)
    {
        $this->failApiCodes($path, $newDevice, 5, 2);

        $this->assertEquals(429, $this->sendApiCode($path, $this->code($newDevice, true), 2)->getStatusCode());
    }

    #[Test]
    #[DataProvider('apiEndpoints')]
    public function the_right_code_is_accepted_below_the_limit(string $path, bool $newDevice)
    {
        $this->failApiCodes($path, $newDevice, 4, 2);

        $this->assertEquals(200, $this->sendApiCode($path, $this->code($newDevice, true), 2)->getStatusCode());
    }

    #[Test]
    #[DataProvider('apiEndpoints')]
    public function the_user_limit_applies_from_every_address(string $path, bool $newDevice)
    {
        $this->failApiCodes($path, $newDevice, 5, 2, '192.0.2.1');

        $this->assertEquals(429, $this->sendApiCode($path, $this->code($newDevice, true), 2, '192.0.2.2')->getStatusCode());
    }

    #[Test]
    #[DataProvider('apiEndpoints')]
    public function another_user_is_not_limited_by_someone_elses_failures(string $path, bool $newDevice)
    {
        $this->failApiCodes($path, $newDevice, 5, 2, '192.0.2.1');

        $this->assertEquals(200, $this->sendApiCode($path, $this->code($newDevice, true), 3, '192.0.2.2')->getStatusCode());
    }

    #[Test]
    public function an_address_trying_many_users_is_limited()
    {
        $path = '/api/users/twofactor/verify-current';

        foreach (range(2, 7) as $userId) {
            $this->failApiCodes($path, false, 5, $userId);
        }

        $this->assertEquals(429, $this->sendApiCode($path, $this->correctCode(), 8)->getStatusCode());
        $this->assertEquals(200, $this->sendApiCode($path, $this->correctCode(), 8, '192.0.2.2')->getStatusCode());
    }

    #[Test]
    public function the_limit_expires()
    {
        LoginThrottler::$decaySeconds = 3;

        $path = '/api/users/twofactor/verify-current';

        $this->failApiCodes($path, false, 5, 2);
        $this->assertEquals(429, $this->sendApiCode($path, $this->correctCode(), 2)->getStatusCode());

        Carbon::setTestNow(Carbon::now()->addSeconds(4));

        $this->assertEquals(200, $this->sendApiCode($path, $this->correctCode(), 2)->getStatusCode());
    }

    #[Test]
    public function wrong_codes_on_the_password_reset_form_are_limited()
    {
        foreach (range(1, 5) as $attempt) {
            $response = $this->resetPassword($this->wrongCode());

            $this->assertEquals(302, $response->getStatusCode(), "Attempt $attempt");
            $this->assertStringContainsString('/reset/resettoken', $response->getHeaderLine('Location'));
        }

        $response = $this->resetPassword($this->correctCode());

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/reset/resettoken', $response->getHeaderLine('Location'));
        $this->assertTrue(User::find(2)->checkPassword('too-obscure'), 'The password must not change while limited.');

        $page = $this->send($this->request('GET', '/reset/resettoken', ['cookiesFrom' => $response]));
        $this->assertStringContainsString('Too many incorrect', (string) $page->getBody());
    }

    #[Test]
    public function the_password_reset_form_accepts_the_right_code_below_the_limit()
    {
        foreach (range(1, 4) as $attempt) {
            $this->resetPassword($this->wrongCode());
        }

        $response = $this->resetPassword($this->correctCode());

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringNotContainsString('/reset/', $response->getHeaderLine('Location'));
        $this->assertTrue(User::find(2)->checkPassword('new-password-123'));
    }

    #[Test]
    public function the_password_reset_form_and_the_api_share_one_limit()
    {
        $this->failApiCodes('/api/users/twofactor/verify-current', false, 5, 2);

        $this->resetPassword($this->correctCode());

        $this->assertTrue(User::find(2)->checkPassword('too-obscure'));
    }

    #[Test]
    public function the_password_reset_page_marks_the_visitor_as_a_guest()
    {
        $response = $this->send($this->request('GET', '/reset/resettoken'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('<html class="guest-user"', (string) $response->getBody());
    }

    #[Test]
    public function attempts_are_counted_before_the_code_is_checked()
    {
        $throttler = $this->app()->getContainer()->make(CodeVerificationThrottler::class);
        $user = User::find(2);
        $request = (new ServerRequest())->withAttribute('ipAddress', '127.0.0.1');

        foreach (range(1, 5) as $attempt) {
            $this->assertTrue($throttler->attempt($user, $request), "Attempt $attempt");
        }

        $this->assertFalse($throttler->attempt($user, $request));

        $throttler->succeeded($user, $request);

        $this->assertTrue($throttler->attempt($user, $request));
    }

    #[Test]
    public function an_attempt_is_refused_while_another_holds_the_lock()
    {
        $container = $this->app()->getContainer();
        $throttler = $container->make(CodeVerificationThrottler::class);
        $lock = $container->make(Repository::class)->getStore()->lock('twofactor-code-attempts:2:lock', 10);
        $this->assertTrue($lock->get());

        try {
            $this->assertFalse($throttler->attempt(User::find(2), (new ServerRequest())->withAttribute('ipAddress', '127.0.0.1')));
        } finally {
            $lock->release();
        }
    }

    #[Test]
    public function a_success_only_refunds_the_window_that_counted_it()
    {
        $limiter = $this->app()->getContainer()->make(RateLimiter::class);
        $throttler = $this->app()->getContainer()->make(CodeVerificationThrottler::class);
        $user = User::find(2);
        $request = (new ServerRequest())->withAttribute('ipAddress', '127.0.0.1');
        $keys = ['twofactor-code-attempts:2', 'twofactor-code-attempts-ip:'.sha1('127.0.0.1')];

        $this->assertTrue($throttler->attempt($user, $request));

        // The window ends while the code is checked, and another attempt starts the next.
        Carbon::setTestNow(Carbon::now()->addSeconds(LoginThrottler::$ipDecaySeconds + 1));
        foreach ($keys as $key) {
            $limiter->clear($key);
        }
        $limiter->hit($keys[0], LoginThrottler::$decaySeconds);

        $throttler->succeeded($user, $request);

        $this->assertSame(1, (int) $limiter->attempts($keys[0]));
        $this->assertSame(0, (int) $limiter->attempts($keys[1]));
    }

    #[Test]
    public function an_empty_code_is_not_counted()
    {
        foreach (range(1, 8) as $attempt) {
            $this->assertEquals(422, $this->sendApiCode('/api/users/twofactor/verify-current', '', 2)->getStatusCode());
        }

        $this->assertEquals(200, $this->sendApiCode('/api/users/twofactor/verify-current', $this->correctCode(), 2)->getStatusCode());
    }

    #[Test]
    #[DataProvider('apiEndpoints')]
    public function guests_are_refused(string $path, bool $newDevice)
    {
        $response = $this->send($this->request('POST', $path, ['json' => ['token' => '000000']]));

        $this->assertEquals(401, $response->getStatusCode());
    }

    #[Test]
    public function an_expired_password_reset_link_is_refused()
    {
        $this->prepareDatabase([
            PasswordToken::class => [
                ['token' => 'oldtoken', 'user_id' => 3, 'created_at' => Carbon::now()->subDays(2)],
            ],
        ]);

        $response = $this->send(
            (new ServerRequest(['REMOTE_ADDR' => '127.0.0.1'], [], '/reset', 'POST'))
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
                ->withParsedBody([
                    'passwordToken' => 'oldtoken',
                    'password' => 'new-password-123',
                    'password_confirmation' => 'new-password-123',
                    'twoFactorToken' => $this->correctCode(),
                ])
        );

        $this->assertNotEquals(302, $response->getStatusCode());
        $this->assertTrue(User::find(3)->checkPassword('too-obscure'));
    }

    protected function failApiCodes(string $path, bool $newDevice, int $times, int $userId, string $ip = '127.0.0.1'): void
    {
        foreach (range(1, $times) as $attempt) {
            $this->assertEquals(422, $this->sendApiCode($path, $this->code($newDevice, false), $userId, $ip)->getStatusCode(), "Wrong code $attempt for user $userId");
        }
    }

    protected function code(bool $newDevice, bool $correct): string
    {
        $secret = $newDevice ? self::NEW_SECRET : $this->secret;

        return $correct ? $this->correctCode($secret) : $this->wrongCode($secret);
    }

    protected function sendApiCode(string $path, string $code, int $userId, string $ip = '127.0.0.1'): ResponseInterface
    {
        $body = ['token' => $code, 'reVerify' => true];

        if (str_contains($path, 'change-device')) {
            $body['changeToken'] = $this->app()->getContainer()->make(DeviceChangeGrant::class)->issue(User::find($userId));
        }

        $request = $this->requestWithJsonBody(new ServerRequest(['REMOTE_ADDR' => $ip], [], $path, 'POST'), $body);

        return $this->send($this->requestAsUser($request, $userId));
    }

    protected function resetPassword(string $code): ResponseInterface
    {
        return $this->send(
            (new ServerRequest(['REMOTE_ADDR' => '127.0.0.1'], [], '/reset', 'POST'))
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
                ->withParsedBody([
                    'passwordToken' => 'resettoken',
                    'password' => 'new-password-123',
                    'password_confirmation' => 'new-password-123',
                    'twoFactorToken' => $code,
                ])
        );
    }
}
