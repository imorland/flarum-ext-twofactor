<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

// MySQL and MariaDB index a foreign key column already. Adding another index
// replaces that one, and down() then can't drop it (error 1553).
$needsIndex = fn (Builder $schema) => ! in_array($schema->getConnection()->getDriverName(), ['mysql', 'mariadb'], true);

return [
    'up' => function (Builder $schema) use ($needsIndex) {
        if ($needsIndex($schema)) {
            $schema->table('two_factor', function (Blueprint $table) {
                $table->index('user_id');
            });
        }
    },

    'down' => function (Builder $schema) use ($needsIndex) {
        if ($needsIndex($schema)) {
            $schema->table('two_factor', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
            });
        }
    },
];
