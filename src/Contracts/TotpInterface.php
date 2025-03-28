<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Contracts;

use Flarum\User\User;

interface TotpInterface
{
    public function createSecret(): string;

    public function getProvisioningUri(string $label, ?string $secret = null): string;

    public function verify(string $secret, string $inputCode, User $user): bool;
}
