<?php

namespace IanM\TwoFactor\Services;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use IanM\TwoFactor\Contracts\TotpInterface;
use OTPHP\TOTP;

class OtpWrapper implements TotpInterface
{
    private TOTP $totp;

    public function __construct(protected SettingsRepositoryInterface $settings, protected BackupCodeGenerator $backupCodes)
    {
        $this->totp = TOTP::create();
        $this->totp->setIssuer($this->settings->get('forum_title'));
    }

    public function setLabel(string $label): void
    {
        $this->totp->setLabel($label);
    }

    public function createSecret(): string
    {
        return $this->totp->getSecret();
    }

    public function getProvisioningUri(string $label, string $secret = null): string
    {
        if (!empty($secret)) {
            $this->createFromSecret($secret);
        }

        $this->setLabel($label);


        return $this->totp->getProvisioningUri();
    }

    public function verify(string $secret, string $inputCode, User $user): bool
    {
        $this->totp = $this->totp->createFromSecret($secret);
        $valid = $this->totp->verify(otp: $inputCode, leeway: 10);

        if ($valid) {
            return true;
        }

        return $this->backupCodes->validateBackupCode($user, $inputCode);
    }

    private function createFromSecret(string $secret): void
    {
        $this->totp = TOTP::create($secret);
        $this->totp->setIssuer($this->settings->get('forum_title'));
    }
}
