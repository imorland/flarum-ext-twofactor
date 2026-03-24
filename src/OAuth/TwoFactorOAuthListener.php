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

use Flarum\User\LoginProvider;
use Flarum\User\User;
use FoF\OAuth\Controllers\AbstractOAuthController;
use FoF\OAuth\Events\OAuthLoginSuccessful;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\OAuth\DisabledProviderRegistry;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Illuminate\Contracts\Cache\Store as CacheStore;

class TwoFactorOAuthListener
{
    use TwoFactorAuthenticationTrait;

    /**
     * Session key set when 2FA is required, read by TwoFactorOAuthCheck middleware.
     */
    public const SESSION_KEY_INTERCEPT = 'twofa_intercept';

    public function __construct(
        protected TotpInterface $totp,
        protected CacheStore $cache,
        protected DisabledProviderRegistry $disabledProviders,
    ) {
    }

    public function handle(OAuthLoginSuccessful $event): void
    {
        // fof/oauth has already regenerated the session by the time this event fires.
        // Retrieve the request (bound by fof/oauth's BindRequest middleware) to access
        // the post-regeneration session.
        /** @var \Psr\Http\Message\ServerRequestInterface $request */
        $request = resolve('fof-oauth-request');

        /** @var \Illuminate\Session\Store $session */
        $session = $request->getAttribute('session');

        // If the user already verified 2FA for this OAuth flow, allow the fast-track
        // resume to complete without intercepting again.
        if ($session->pull(TwoFactorOAuthCheck::CACHE_KEY_CLEARED)) {
            return;
        }

        $user = $this->getUserFromProvider($event->providerName, $event->identifier);

        if (! $user || $this->disabledProviders->isDisabled($event->providerName) || ! $this->twoFactorActive($user)) {
            return;
        }

        // Store oauth_data in cache under the new session ID for fof/oauth's fast-track.
        $this->cache->put(
            $this->cacheKey(AbstractOAuthController::SESSION_OAUTH_DATA, $session),
            [
                'token' => $event->token,
                'resourceOwner' => $event->userResource,
                'provider' => $event->providerName,
                'userId' => $user->id,
            ],
            AbstractOAuthController::$OAUTH_DATA_CACHE_LIFETIME
        );

        // Signal the middleware (which runs after this event) to redirect to the 2FA form.
        $session->put(self::SESSION_KEY_INTERCEPT, $event->providerName);
    }

    protected function getUserFromProvider(string $provider, string $identifier): ?User
    {
        $loginProvider = LoginProvider::where('provider', $provider)
            ->where('identifier', $identifier)
            ->first();

        return $loginProvider?->user;
    }

    protected function cacheKey(string $key, \Illuminate\Session\Store $session): string
    {
        return "{$key}_{$session->getId()}";
    }
}
