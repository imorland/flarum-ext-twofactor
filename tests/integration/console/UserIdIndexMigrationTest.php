<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Tests\integration\console;

use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserIdIndexMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('ianm-twofactor');
    }

    #[Test]
    public function two_factor_records_are_indexed_by_user_and_the_migration_can_be_undone()
    {
        $schema = $this->database()->getSchemaBuilder();
        $migration = require __DIR__.'/../../../migrations/2026_11_24_000001_add_user_id_index_to_two_factor.php';

        $this->assertTrue($this->userIdIsIndexed($schema));

        $migration['down']($schema);
        $migration['up']($schema);

        $this->assertTrue($this->userIdIsIndexed($schema));
    }

    protected function userIdIsIndexed($schema): bool
    {
        return collect($schema->getIndexes('two_factor'))
            ->contains(fn (array $index) => $index['columns'] === ['user_id']);
    }
}
