<?php

namespace IanM\TwoFactor\Data;

use Blomstra\Gdpr\Data\Type;
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
        return Arr::except($twoFactor->toArray(), ['id', 'user_id', 'secret']);
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
