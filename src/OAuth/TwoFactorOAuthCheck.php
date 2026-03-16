<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\OAuth;

use Flarum\Http\UrlGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;

class TwoFactorOAuthCheck implements MiddlewareInterface
{
    /**
     * Cache key used to signal that 2FA has been verified for this session,
     * so the middleware does not intercept the fast-track resume pass.
     */
    public const CACHE_KEY_CLEARED = 'twofa_cleared';

    public function __construct(protected UrlGenerator $url)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        /** @var \Illuminate\Session\Store $session */
        $session = $request->getAttribute('session');

        // TwoFactorOAuthListener sets this after a successful OAuth exchange when the
        // user has 2FA enabled. The value is the provider name for the fast-track redirect.
        $providerName = $session->pull(TwoFactorOAuthListener::SESSION_KEY_INTERCEPT);

        if ($providerName === null) {
            return $response;
        }

        return new RedirectResponse($this->url->to('forum')->route('twoFactor.oauth'));
    }
}
