<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Tests\integration;

use Carbon\Carbon;
use OTPHP\TOTP;

trait GeneratesTwoFactorCodes
{
    protected string $secret = 'OIZ2R42HL2ZNUJNJU72P4EK26CQSD5JLEC7AVH7BCBJKRCUBUPLHXQ4TCAYVFZPDAGH3QDPHWABLMT36QAKTIFPNL5NKTR2BGVIY3GY';

    protected function twoFactorRow(int $userId, ?int $id = null, ?string $secret = null): array
    {
        return [
            'id' => $id ?? $userId,
            'user_id' => $userId,
            'secret' => $secret ?? $this->secret,
            'backup_codes' => '[]',
            'is_active' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    protected function correctCode(?string $secret = null): string
    {
        return TOTP::createFromSecret($secret ?? $this->secret)->now();
    }

    /**
     * A code that isn't accepted for the secret at any time inside the verification leeway.
     */
    protected function wrongCode(?string $secret = null): string
    {
        $totp = TOTP::createFromSecret($secret ?? $this->secret);
        $now = time();
        $valid = [$totp->at($now - 30), $totp->at($now), $totp->at($now + 30)];

        for ($candidate = 0;; $candidate++) {
            $code = str_pad((string) $candidate, 6, '0', STR_PAD_LEFT);

            if (! in_array($code, $valid, true)) {
                return $code;
            }
        }
    }
}
