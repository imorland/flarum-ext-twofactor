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
use Flarum\User\User;
use FoF\OAuth\Controllers\AbstractOAuthController;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\OAuth\TwoFactorOAuthCheck;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Illuminate\Contracts\Cache\Store as CacheStore;
use Illuminate\Session\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TwoFactorOAuthVerifyController implements RequestHandlerInterface
{
    use TwoFactorAuthenticationTrait;

    public function __construct(
        protected UrlGenerator $url,
        protected TotpInterface $totp,
        protected CacheStore $cache
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Store $session */
        $session = $request->getAttribute('session');

        $twoFactorToken = Arr::get($request->getParsedBody(), 'twoFactorToken');

        if (empty($twoFactorToken)) {
            $session->put('errors', new MessageBag(['twoFactorToken' => '2FA token required']));

            return new RedirectResponse($this->url->to('forum')->route('twoFactor.oauth'));
        }

        $oauthData = $this->cache->get($this->cacheKey(AbstractOAuthController::SESSION_OAUTH_DATA, $session));

        if ($oauthData === null) {
            $session->put('errors', new MessageBag(['twoFactorToken' => app('translator')->trans('ianm-twofactor.views.two_factor_token.oauth_session_expired')]));

            return new RedirectResponse($this->url->to('forum')->route('twoFactor.oauth'));
        }

        $userId = Arr::get($oauthData, 'userId');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            $session->put('errors', new MessageBag(['twoFactorToken' => app('translator')->trans('ianm-twofactor.views.two_factor_token.oauth_session_expired')]));

            return new RedirectResponse($this->url->to('forum')->route('twoFactor.oauth'));
        }

        $token = $this->retrieveTwoFactorTokenFrom($twoFactorToken);

        if (! $this->isTokenActive($token, $user)) {
            $session->put('errors', new MessageBag(['twoFactorToken' => 'Invalid 2FA token']));

            return new RedirectResponse($this->url->to('forum')->route('twoFactor.oauth'));
        }

        // Mark 2FA as verified in the session so the middleware allows the fast-track
        // resume to complete. Using the session (not cache) so the flag survives session
        // ID regeneration that happens inside fof/oauth's handleOAuthResponse.
        $session->put(TwoFactorOAuthCheck::CACHE_KEY_CLEARED, true);

        // fof/oauth's fast-track will detect the cached oauth_data and resume the login flow.
        $providerName = Arr::get($oauthData, 'provider');

        return new RedirectResponse(
            $this->url->to('forum')->route('fof-oauth', ['provider' => $providerName])
        );
    }

    protected function cacheKey(string $key, Store $session): string
    {
        return "{$key}_{$session->getId()}";
    }
}
