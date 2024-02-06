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

use Flarum\Foundation\ValidationException;
use Flarum\Http\RememberAccessToken;
use Flarum\Http\SessionAccessToken;
use Flarum\User\Exception\NotAuthenticatedException;
use Flarum\User\UserRepository;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateTwoFactorTokenController implements RequestHandlerInterface
{
    use TwoFactorAuthenticationTrait;

    public function __construct(
        protected TotpInterface $totp,
        protected UserRepository $users,
        protected BusDispatcher $bus,
        protected EventDispatcher $events
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();

        $identification = Arr::get($body, 'identification');
        $password = Arr::get($body, 'password');

        $user = $this->users->findByIdentification($identification);

        if (! $user || ! $user->checkPassword($password)) {
            throw new NotAuthenticatedException();
        }

        if ($this->twoFactorActive($user)) {
            $token = $this->retrieveTwoFactorTokenFrom(Arr::get($body, 'twoFactorToken'));

            if (! $token) {
                throw new ValidationException(['twoFactorToken' => 'two_factor_required']);
            }

            if (! $this->isTokenActive($token, $user)) {
                throw new ValidationException(['twoFactorToken' => 'two_factor_incorrect']);
            }
        }

        if (Arr::get($body, 'remember')) {
            $token = RememberAccessToken::generate($user->id);
        } else {
            $token = SessionAccessToken::generate($user->id);
        }

        $token->touch($request);

        return new JsonResponse([
            'token' => $token->token,
            'userId' => $user->id
        ]);
    }
}
