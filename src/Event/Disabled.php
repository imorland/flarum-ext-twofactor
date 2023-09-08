<?php

namespace IanM\TwoFactor\Event;

use Flarum\User\User;

class Disabled
{
    public function __construct(public User $user, public User $actor)
    {
    }
}
