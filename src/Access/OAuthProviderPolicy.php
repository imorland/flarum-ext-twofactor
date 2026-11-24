<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Access;

use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;
use IanM\TwoFactor\Services\TwoFactorRestrictor;

/**
 * A group's 2FA requirement is otherwise enforced only by the forum's UI, which
 * applications connected through flarum/oauth-provider never show.
 */
class OAuthProviderPolicy extends AbstractPolicy
{
    public function __construct(
        protected TwoFactorRestrictor $restrictor
    ) {
    }

    public function can(User $actor, string $ability): ?string
    {
        if ($ability === 'oauthProvider.connect' && ! $actor->isGuest() && $this->restrictor->mustEnableTwoFactor($actor)) {
            return $this->deny();
        }

        return null;
    }
}
