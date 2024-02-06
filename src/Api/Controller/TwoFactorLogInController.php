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

use Flarum\Api\Client;
use Flarum\Extension\ExtensionManager;
use Flarum\Forum\Controller\LogInController;
use Flarum\Forum\LogInValidator;
use Flarum\Http\AccessToken;
use Flarum\Http\RememberAccessToken;
use Flarum\Http\Rememberer;
use Flarum\Http\SessionAuthenticator;
use Flarum\User\Event\LoggedIn;
use Flarum\User\UserRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class TwoFactorLogInController extends LogInController
{
    public function __construct(
        UserRepository $users,
        Client $apiClient,
        SessionAuthenticator $authenticator,
        Dispatcher $events,
        Rememberer $rememberer,
        LogInValidator $validator,
        protected ExtensionManager $extensions
    ) {
        parent::__construct($users, $apiClient, $authenticator, $events, $rememberer, $validator);
    }

    public function handle(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();

        if (! $this->extensions->isEnabled('blomstra-turnstile') && empty(Arr::get($body, 'twoFactorToken'))) {
            $this->validator->assertValid($body);
        }

        $response = $this->apiClient->withParentRequest($request)
            ->withBody($body)
            ->post('/token');

        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody());

            $token = AccessToken::findValid($data->token);

            $session = $request->getAttribute('session');
            $this->authenticator->logIn($session, $token);

            $this->events->dispatch(new LoggedIn($this->users->findOrFail($data->userId), $token));

            if ($token instanceof RememberAccessToken) {
                $response = $this->rememberer->remember($response, $token);
            }
        }

        return $response;
    }
}
