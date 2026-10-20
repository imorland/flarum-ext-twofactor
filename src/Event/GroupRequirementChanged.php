<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Event;

use Flarum\Group\Group;
use Flarum\User\User;

class GroupRequirementChanged
{
    public function __construct(public Group $group, public User $actor, public bool $required)
    {
    }
}
