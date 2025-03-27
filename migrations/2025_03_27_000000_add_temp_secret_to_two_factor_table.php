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

return [
    'up' => function (Builder $schema) {
        if ($schema->hasTable('two_factor')) {
            $schema->table('two_factor', function (Blueprint $table) {
                $table->string('temp_secret')->nullable();
                $table->timestamp('temp_secret_created_at')->nullable();
            });
        }
    },
    'down' => function (Builder $schema) {
        if ($schema->hasTable('two_factor')) {
            $schema->table('two_factor', function (Blueprint $table) {
                $table->dropColumn('temp_secret');
                $table->dropColumn('temp_secret_created_at');
            });
        }
    }
];
