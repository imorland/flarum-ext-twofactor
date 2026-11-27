<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Data;

use Flarum\Gdpr\Data\Type;
use IanM\TwoFactor\Model\TwoFactor;
use Illuminate\Support\Arr;

class TwoFactorData extends Type
{
    public function export(): ?array
    {
        $record = TwoFactor::query()
            ->where('user_id', $this->user->id)
            ->first();

        if ($record) {
            return ["2fa/{$this->user->id}.json" => $this->encodeForExport($this->sanitize($record))];
        }

        return [];
    }

    protected function sanitize(TwoFactor $twoFactor): array
    {
        // Anyone with the user's session can request an export, and the secrets
        // and backup code hashes would let them make or crack a code.
        return Arr::only($twoFactor->toArray(), ['is_active', 'created_at', 'updated_at']);
    }

    public static function anonymizeDescription(): string
    {
        return self::deleteDescription();
    }

    public function anonymize(): void
    {
        $this->delete();
    }

    public function delete(): void
    {
        TwoFactor::query()
            ->where('user_id', $this->user->id)
            ->delete();
    }
}
