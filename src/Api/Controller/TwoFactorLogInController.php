<?php

namespace IanM\TwoFactor\Api\Controller;

use Flarum\Forum\Controller\LogInController;
use Flarum\Http\AccessToken;
use Flarum\Http\RememberAccessToken;
use Flarum\User\Event\LoggedIn;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class TwoFactorLogInController extends LogInController
{
    public function handle(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $identification = Arr::get($body, 'identification');
        $password = Arr::get($body, 'password');
        $remember = Arr::get($body, 'remember');
        $twoFactorToken = Arr::get($body, 'twoFactorToken');

        $this->validator->assertValid($body);

        $response = $this->apiClient->withParentRequest($request)
            ->withBody([
                'identification' => $identification,
                'password' => $password,
                'remember' => $remember,
                'twoFactorToken' => $twoFactorToken])
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
