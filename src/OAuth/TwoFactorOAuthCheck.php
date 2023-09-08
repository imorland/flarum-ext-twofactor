<?php

namespace IanM\TwoFactor\OAuth;

use Flarum\Http\UrlGenerator;
use Flarum\User\LoginProvider;
use Flarum\User\User;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Illuminate\Session\Store;
use Illuminate\Support\MessageBag;
use Laminas\Diactoros\Response\RedirectResponse;

class TwoFactorOAuthCheck
{
    use TwoFactorAuthenticationTrait;
    
    public function __construct(protected TotpInterface $totp, protected UrlGenerator $url)
    {
    }

    public function __invoke(ServerRequestInterface $request, AccessTokenInterface $token, ResourceOwnerInterface $resourceOwner, string $provider)
    {
        $user = $this->getUserFromProvider($provider, $resourceOwner);

        if (!$user) {
            return;
        }

        /** @var Store */
        $session = $request->getAttribute('session');

        if ($this->twoFactorActive($user)) {
            
            if ($session->has('twoFactorToken')) {
                return $this->handle2FASubmission($session);
            } else {
                $session->put('oauth_data', [
                    'token' => $token,
                    'resourceOwner' => $resourceOwner,
                    'provider' => $provider,
                    'userId' => $user->id,
                    'requestUri' => $request->getUri(),
                ]);
    
                // Redirect to a 2FA form
                return new RedirectResponse($this->url->to('forum')->route('twoFactor.oauth'));
            }
        }
    }

    public function handle2FASubmission(Store $session)
    {
        $token = $this->retrieveTwoFactorTokenFrom($session->get('twoFactorToken'));
        $oauthData = $session->get('oauth_data');
        
        $user = $this->getUserFromProvider($oauthData['provider'], $oauthData['resourceOwner']);

        if (!$this->isTokenActive($token, $user)) {
            $session->put('errors', new MessageBag(['twoFactorToken' => 'Invalid 2FA token']));
            return new RedirectResponse($this->url->to('forum')->route('twoFactor.oauth'));
        }

        $session->remove('oauth_data');
    }

    protected function getUserFromProvider(string $provider, ResourceOwnerInterface $resourceOwner): ?User
    {
        $provider = LoginProvider::where('provider', $provider)->where('identifier', $resourceOwner->getId())->first();

        if ($provider) {
            return $provider->user;
        }

        return null;
    }
}
