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

use DateTime;
use Flarum\Forum\Controller\ResetPasswordController;
use Flarum\User\Exception\InvalidConfirmationTokenException;
use Flarum\User\PasswordToken;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface as Request;

class TwoFactorResetPasswordController extends ResetPasswordController
{
    use TwoFactorAuthenticationTrait;

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Flarum\User\Exception\InvalidConfirmationTokenException
     */
    public function render(Request $request)
    {
        $token = Arr::get($request->getQueryParams(), 'token');

        $token = PasswordToken::findOrFail($token);

        if ($token->created_at < new DateTime('-1 day')) {
            throw new InvalidConfirmationTokenException();
        }

        $hasTwoFactorEnabled = $this->twoFactorActive($token->user);

        return $this->view->make('ianm-two-factor::reset-password')
            ->with('passwordToken', $token->token)
            ->with('csrfToken', $request->getAttribute('session')->token())
            ->with('hasTwoFactorEnabled', $hasTwoFactorEnabled);
    }
}
