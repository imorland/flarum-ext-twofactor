<?php

namespace IanM\TwoFactor\Event;

use Flarum\User\User;

class Enabled
{
    public function __construct(public User $user, public bool $withBackupCodes = false)
    { 
    }
}
