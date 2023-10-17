<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Services;

use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use Illuminate\Contracts\Hashing\Hasher;

class BackupCodeGenerator
{
    public function __construct(protected Hasher $hash)
    {
    }

    public function generate(User $user, $count = 5)
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = $this->generateSingleCode();
        }

        $this->saveBackupCodesToDatabase($user, $codes);

        return $codes;
    }

    protected function generateSingleCode()
    {
        return mt_rand(100000, 999999);  // Generate a 6-digit code.
    }

    protected function saveBackupCodesToDatabase(User $user, array $codes, bool $alreadyHashed = false)
    {
        if (! $alreadyHashed) {
            // Hash each backup code
            $hashedCodes = array_map(function ($code) {
                return $this->hash->make($code);
            }, $codes);
        } else {
            $hashedCodes = $codes;
        }

        $twoFactor = TwoFactor::getForUser($user);
        $twoFactor->backup_codes = json_encode($hashedCodes);
        $twoFactor->save();
    }

    public function validateBackupCode(User $user, string $code): bool
    {
        $hashedBackupCodes = json_decode($user->twoFactor->backup_codes, true) ?? [];

        foreach ($hashedBackupCodes as $index => $hashedCode) {
            if ($this->hash->check($code, $hashedCode)) {
                // Remove the used code from the array.
                unset($hashedBackupCodes[$index]);

                // Update the backup codes in the database.
                $this->saveBackupCodesToDatabase($user, $hashedBackupCodes, true);

                return true;
            }
        }

        return false;
    }

    public function getRemainingBackupCodes(User $user): int
    {
        $twoFactor = $user->twoFactor ?? TwoFactor::getForUser($user);

        if ($twoFactor->backup_codes === null) {
            return 0;
        }

        $hashedBackupCodes = json_decode($twoFactor->backup_codes, true);

        return count($hashedBackupCodes);
    }
}
