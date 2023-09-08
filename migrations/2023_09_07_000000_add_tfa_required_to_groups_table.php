<?php

use Flarum\Database\Migration;

return Migration::addColumns('groups', [
    'tfa_required' => ['boolean', 'default' => false, 'nullable' => false]
]);
