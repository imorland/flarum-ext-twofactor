<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Api\Controller;

use Flarum\Forum\Controller\LogInViewController;
use Illuminate\Contracts\View\View;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Maintenance login view, with a field for the 2FA token.
 */
class TwoFactorLogInViewController extends LogInViewController
{
    public function render(Request $request): View
    {
        return $this->view
            ->make('ianm-two-factor::log-in')
            ->with('csrfToken', $request->getAttribute('session')->token());
    }
}
