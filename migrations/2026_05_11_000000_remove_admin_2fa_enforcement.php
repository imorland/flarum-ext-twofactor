<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Flarum\Group\Group;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        // Remove 2FA enforcement from admin group to make it configurable like other groups
        $connection = $schema->getConnection();
        $connection->table('groups')->where('id', Group::ADMINISTRATOR_ID)->update(['tfa_required' => false]);
    },

    'down' => function (Builder $schema) {
        // Not doing anything but `down` has to be defined
    }
];
