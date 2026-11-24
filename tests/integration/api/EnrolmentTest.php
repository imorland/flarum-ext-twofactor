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

use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Tests\integration\GeneratesTwoFactorCodes;
use Illuminate\Contracts\Cache\Repository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class EnrolmentTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use GeneratesTwoFactorCodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'fresh', 'email' => 'fresh@machine.local', 'is_email_confirmed' => 1],
            ],
            TwoFactor::class => [$this->twoFactorRow(2)],
        ]);
    }

    protected function tearDown(): void
    {
        $this->app()->getContainer()->make(Repository::class)->clear();

        parent::tearDown();
    }

    #[Test]
    public function the_secret_is_not_shown_again_once_2fa_is_on()
    {
        $response = $this->send($this->request('GET', '/api/users/2/twofactor/qrcode', ['authenticatedAs' => 2]));

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringNotContainsString($this->secret, (string) $response->getBody());
    }

    #[Test]
    public function the_secret_is_shown_while_setting_up()
    {
        $response = $this->send($this->request('GET', '/api/users/3/twofactor/qrcode', ['authenticatedAs' => 3]));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty(json_decode((string) $response->getBody(), true)['code']);
    }

    #[Test]
    public function enabling_again_does_not_hand_out_new_backup_codes()
    {
        $this->app();
        $before = TwoFactor::query()->where('user_id', 2)->value('backup_codes');

        $response = $this->send($this->request('POST', '/api/users/twofactor/verify', [
            'authenticatedAs' => 2,
            'json' => ['token' => $this->correctCode()],
        ]));

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringNotContainsString('backupCodes', (string) $response->getBody());
        $this->assertSame($before, TwoFactor::query()->where('user_id', 2)->value('backup_codes'));
    }

    #[Test]
    public function verifying_before_a_secret_exists_is_refused()
    {
        $response = $this->send($this->request('POST', '/api/users/twofactor/verify', [
            'authenticatedAs' => 3,
            'json' => ['token' => '123456'],
        ]));

        $this->assertEquals(422, $response->getStatusCode());
    }

    public static function codeEndpoints(): array
    {
        return [
            ['POST', '/api/users/twofactor/verify'],
            ['POST', '/api/users/twofactor/verify-current'],
            ['POST', '/api/users/twofactor/change-device/verify'],
            ['DELETE', '/api/users/2/twofactor/disable'],
        ];
    }

    #[Test]
    #[DataProvider('codeEndpoints')]
    public function a_code_that_is_not_a_string_is_refused(string $method, string $path)
    {
        $response = $this->send($this->request($method, $path, [
            'authenticatedAs' => 2,
            'json' => ['token' => ['123456'], 'changeToken' => ['x']],
        ]));

        $this->assertContains($response->getStatusCode(), [403, 422], (string) $response->getBody());
    }
}
