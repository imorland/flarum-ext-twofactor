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

use Flarum\User\User;

class Disabled
{
    /**
     * @param User|null $actor Null when 2FA was disabled from the console.
     */
    public function __construct(public User $user, public ?User $actor)
    {
    }
}
