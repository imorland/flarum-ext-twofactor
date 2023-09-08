<?php

namespace IanM\TwoFactor\Contracts;

use Flarum\User\User;

interface TotpInterface
{
    public function createSecret(): string;
    public function getProvisioningUri(string $label): string;
    public function verify(string $secret, string $inputCode, User $user): bool;
}
