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

use Flarum\Http\UrlGenerator;
use Illuminate\Session\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TwoFactorOAuthVerifyController implements RequestHandlerInterface
{
    public function __construct(protected UrlGenerator $url)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Store */
        $session = $request->getAttribute('session');

        // Get the provided 2FA token from the request body
        $twoFactorToken = Arr::get($request->getParsedBody(), 'twoFactorToken');

        if (! empty($twoFactorToken)) {
            /** @var \Psr\Http\Message\UriInterface */
            $oauthUri = Arr::get($session->get('oauth_data'), 'requestUri');
            $session->put('fastTrack', true);
            $session->put('twoFactorToken', $twoFactorToken);

            return new RedirectResponse($oauthUri);
        }

        // If the token is missing, redirect them back to the 2FA form with an error message.
        $request->getAttribute('session')->put('errors', new MessageBag(['twoFactorToken' => '2FA token required']));

        return new RedirectResponse($this->url->to('forum')->route('twoFactor.oauth'));
    }
}
