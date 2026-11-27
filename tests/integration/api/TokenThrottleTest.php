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
use Flarum\Api\ApiKey;
use Flarum\Extend;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\Throttler\LoginThrottler;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Tests\integration\GeneratesTwoFactorCodes;
use Illuminate\Contracts\Cache\Repository;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TokenThrottleTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use GeneratesTwoFactorCodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extend(
            (new Extend\Csrf())->exemptRoute('login')
        );

        $this->extension('ianm-twofactor');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            TwoFactor::class => [
                $this->twoFactorRow(2),
            ],
            ApiKey::class => [
                ['key' => 'mastertoken', 'user_id' => null, 'created_at' => Carbon::now()->toDateTimeString()],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->app()->getContainer()->make(Repository::class)->clear();

        LoginThrottler::$decaySeconds = 60;

        parent::tearDown();
    }

    #[Test]
    public function wrong_codes_are_limited_like_wrong_passwords()
    {
        $this->failCodes(5);

        $response = $this->logIn($this->wrongCode());

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals('too_many_requests', json_decode((string) $response->getBody(), true)['errors'][0]['code']);
    }

    #[Test]
    public function the_right_code_is_refused_while_limited()
    {
        $this->failCodes(5);

        $this->assertEquals(429, $this->logIn($this->correctCode())->getStatusCode());
    }

    #[Test]
    public function the_right_code_logs_in_below_the_limit()
    {
        $this->failCodes(4);

        $this->assertEquals(200, $this->logIn($this->correctCode())->getStatusCode());
    }

    #[Test]
    public function wrong_codes_and_wrong_passwords_share_one_limit()
    {
        $this->failCodes(3);

        foreach (range(1, 2) as $attempt) {
            $this->assertEquals(401, $this->send($this->logInRequest(['password' => 'incorrect']))->getStatusCode(), "Attempt $attempt");
        }

        $this->assertEquals(429, $this->logIn($this->correctCode())->getStatusCode());
    }

    #[Test]
    public function a_missing_code_is_not_counted()
    {
        foreach (range(1, 8) as $attempt) {
            $response = $this->logIn(null);

            $this->assertEquals(422, $response->getStatusCode(), "Attempt $attempt");
            $this->assertEquals('two_factor_required', json_decode((string) $response->getBody(), true)['errors'][0]['detail']);
        }

        $this->assertEquals(200, $this->logIn($this->correctCode())->getStatusCode());
    }

    #[Test]
    public function the_login_form_and_the_api_share_one_limit()
    {
        $this->failCodes(3);

        foreach (range(1, 2) as $attempt) {
            $this->assertEquals(422, $this->logIn($this->wrongCode(), '/login')->getStatusCode(), "Attempt $attempt");
        }

        $this->assertEquals(429, $this->logIn($this->correctCode(), '/login')->getStatusCode());
        $this->assertEquals(429, $this->logIn($this->correctCode())->getStatusCode());
    }

    #[Test]
    public function wrong_codes_are_limited_for_the_account_from_every_address()
    {
        foreach (['192.0.2.1', '192.0.2.2', '192.0.2.3', '192.0.2.4', '192.0.2.5'] as $ip) {
            $this->assertEquals(422, $this->logIn($this->wrongCode(), ip: $ip)->getStatusCode());
        }

        $this->assertEquals(429, $this->logIn($this->correctCode(), ip: '192.0.2.6')->getStatusCode());
    }

    #[Test]
    public function wrong_passwords_from_another_address_do_not_limit_the_account()
    {
        foreach (range(1, 5) as $attempt) {
            $this->assertEquals(401, $this->send($this->logInRequest(['password' => 'incorrect'], ip: '192.0.2.1'))->getStatusCode());
        }

        $this->assertEquals(200, $this->logIn($this->correctCode(), ip: '192.0.2.2')->getStatusCode());
    }

    #[Test]
    public function the_limit_expires()
    {
        LoginThrottler::$decaySeconds = 3;

        $this->failCodes(5);
        $this->assertEquals(429, $this->logIn($this->correctCode())->getStatusCode());

        sleep(4);

        $this->assertEquals(200, $this->logIn($this->correctCode())->getStatusCode());
    }

    #[Test]
    public function requests_made_with_an_api_key_are_not_limited()
    {
        foreach (range(1, 8) as $attempt) {
            $response = $this->send(
                $this->logInRequest(['twoFactorToken' => $this->wrongCode()])
                    ->withAddedHeader('Authorization', 'Token mastertoken; userId=1')
            );

            $this->assertEquals(422, $response->getStatusCode(), "Attempt $attempt");
        }

        $this->assertEquals(200, $this->logIn($this->correctCode())->getStatusCode());
    }

    #[Test]
    public function a_request_without_identification_is_not_authenticated()
    {
        $response = $this->send(
            $this->requestWithJsonBody(
                new ServerRequest(['REMOTE_ADDR' => '127.0.0.1'], [], '/api/token', 'POST'),
                ['password' => 'too-obscure']
            )
        );

        $this->assertEquals(401, $response->getStatusCode());
    }

    protected function failCodes(int $times): void
    {
        foreach (range(1, $times) as $attempt) {
            $response = $this->logIn($this->wrongCode());

            $this->assertEquals(422, $response->getStatusCode(), "Wrong code $attempt");
            $this->assertEquals('two_factor_incorrect', json_decode((string) $response->getBody(), true)['errors'][0]['detail']);
        }
    }

    protected function logIn(?string $code, string $path = '/api/token', string $ip = '127.0.0.1'): ResponseInterface
    {
        return $this->send($this->logInRequest($code === null ? [] : ['twoFactorToken' => $code], $path, $ip));
    }

    protected function logInRequest(array $body, string $path = '/api/token', string $ip = '127.0.0.1'): ServerRequestInterface
    {
        return $this->requestWithJsonBody(
            new ServerRequest(['REMOTE_ADDR' => $ip], [], $path, 'POST'),
            array_merge(['identification' => 'normal', 'password' => 'too-obscure'], $body)
        );
    }
}
