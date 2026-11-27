<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Tests\integration\gdpr;

use Carbon\Carbon;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use IanM\TwoFactor\Data\TwoFactorData;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Tests\integration\GeneratesTwoFactorCodes;
use PHPUnit\Framework\Attributes\Test;

class TwoFactorDataTest extends TestCase
{
    use RetrievesAuthorizedUsers;
    use GeneratesTwoFactorCodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-gdpr', 'ianm-twofactor');

        $this->prepareDatabase([
            User::class => [$this->normalUser()],
            TwoFactor::class => [
                [
                    'backup_codes' => json_encode([password_hash('246810', PASSWORD_BCRYPT)]),
                    'temp_secret' => 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP',
                    'temp_secret_created_at' => Carbon::now(),
                ] + $this->twoFactorRow(2),
            ],
        ]);
    }

    /**
     * An export only needs a session, so it must not give out anything that
     * could be turned into a code.
     */
    #[Test]
    public function the_export_leaves_out_secrets_and_backup_codes()
    {
        $container = $this->app()->getContainer();
        $data = $container->make(TwoFactorData::class, ['user' => User::find(2), 'erasureRequest' => null]);

        $export = json_decode(array_values($data->export())[0], true);

        $this->assertTrue($export['is_active']);
        $this->assertEmpty(array_intersect(array_keys($export), ['secret', 'backup_codes', 'temp_secret', 'temp_secret_created_at']));
        $this->assertStringNotContainsString('$2y$', json_encode($export));
    }
}
