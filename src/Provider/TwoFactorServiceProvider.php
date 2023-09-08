<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Provider;

use Flarum\Foundation\AbstractServiceProvider;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Services\BackupCodeGenerator;
use IanM\TwoFactor\Services\OtpWrapper;
use IanM\TwoFactor\Services\QrCodeGenerator;
use IanM\TwoFactor\Services\TwoFactorRestrictor;

class TwoFactorServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        $this->container->bind(TotpInterface::class, OtpWrapper::class);
        $this->container->bind(QrCodeGenerator::class);
        $this->container->bind(BackupCodeGenerator::class);
        $this->container->bind(TwoFactorRestrictor::class);
    }

    public function boot()
    {
    }
}
