<?php

namespace IanM\TwoFactor\Tests\Integration\OAuth\Stubs;

use League\OAuth2\Client\Token\AccessTokenInterface;

class FakeAccessToken implements AccessTokenInterface
{
    public function getToken(): string
    {
        return 'fake-access-token';
    }

    public function getRefreshToken(): ?string
    {
        return null;
    }

    public function getExpires(): ?int
    {
        return null;
    }

    public function hasExpired(): bool
    {
        return false;
    }

    public function getValues(): array
    {
        return [];
    }

    public function __toString(): string
    {
        return $this->getToken();
    }

    public function jsonSerialize(): mixed
    {
        return ['access_token' => $this->getToken()];
    }
}
