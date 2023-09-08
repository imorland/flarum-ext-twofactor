<?php

use Flarum\Group\Group;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        // Set 2fa_required to true for the group with ID Group::ADMINISTRATOR_ID
        $connection = $schema->getConnection();
        $connection->table('groups')->where('id', Group::ADMINISTRATOR_ID)->update(['tfa_required' => true]);
    },

    'down' => function (Builder $schema) {
        // nothing.
    }
];
