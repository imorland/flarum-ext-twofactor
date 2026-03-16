<?php

namespace IanM\TwoFactor\Tests\Integration\OAuth\Stubs;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class FakeResourceOwner implements ResourceOwnerInterface
{
    public function __construct(private string $id = 'fake-id')
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return ['id' => $this->id];
    }
}
