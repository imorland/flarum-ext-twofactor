<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Extend;

use Flarum\Extend\ExtenderInterface;
use Flarum\Extension\Extension;
use IanM\TwoFactor\OAuth\DisabledProviderRegistry;
use Illuminate\Contracts\Container\Container;

class TwoFactor implements ExtenderInterface
{
    private array $disabledProviders = [];

    public function disable(string $provider): static
    {
        $this->disabledProviders[] = $provider;

        return $this;
    }

    public function extend(Container $container, ?Extension $extension = null): void
    {
        $container->extend(
            DisabledProviderRegistry::class,
            function (DisabledProviderRegistry $registry) {
                foreach ($this->disabledProviders as $provider) {
                    $registry->disable($provider);
                }

                return $registry;
            }
        );
    }
}
