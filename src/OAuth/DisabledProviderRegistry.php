<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\OAuth;

class DisabledProviderRegistry
{
    private array $disabled = [];

    public function disable(string $provider): void
    {
        $this->disabled[] = $provider;
    }

    public function isDisabled(string $provider): bool
    {
        return in_array($provider, $this->disabled, true);
    }
}
