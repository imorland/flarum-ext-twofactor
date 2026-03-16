<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

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
