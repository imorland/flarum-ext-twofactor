<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Tests\integration\mcp;

use Flarum\Mcp\Capability\CapabilityRegistry;
use Flarum\Mcp\Testing\McpTestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

class McpTest extends McpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');

        $this->prepareDatabase([
            User::class => [$this->normalUser()],
        ]);
    }

    #[Test]
    public function the_two_factor_type_is_not_offered_to_assistants(): void
    {
        $auth = $this->bearer($this->developerToken(2));

        $types = array_column($this->toolOutput($this->callTool('describe_api', [], $auth))['types'], 'type');

        $this->assertContains('users', $types);
        $this->assertNotContains('two-factor', $types);
        $this->assertTrue($this->app()->getContainer()->make(CapabilityRegistry::class)->isHidden('two-factor'));
    }
}
