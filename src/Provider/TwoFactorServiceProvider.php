<?php

namespace IanM\TwoFactor\Provider;

use Flarum\Foundation\AbstractServiceProvider;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Services\TwoFactorRestrictor;
use IanM\TwoFactor\Services\BackupCodeGenerator;
use IanM\TwoFactor\Services\OtpWrapper;
use IanM\TwoFactor\Services\QrCodeGenerator;

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
