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

use Flarum\Http\Controller\AbstractHtmlController;
use Illuminate\Contracts\View\Factory;
use Psr\Http\Message\ServerRequestInterface;

class TwoFactorOAuthController extends AbstractHtmlController
{
    public function __construct(protected Factory $view)
    {
    }

    protected function render(ServerRequestInterface $request)
    {
        return $this->view->make('ianm-two-factor::oauth.verify')
            ->with('csrfToken', $request->getAttribute('session')->token());
    }
}
