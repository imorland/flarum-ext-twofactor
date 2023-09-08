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

    protected $users;
    protected $bus;
    protected $events;
    protected $totp;

    public function __construct(TotpInterface $totp, UserRepository $users, BusDispatcher $bus, EventDispatcher $events)
    {
        $this->users = $users;
        $this->bus = $bus;
        $this->events = $events;
        $this->totp = $totp;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();

        $identification = Arr::get($body, 'identification');
        $password = Arr::get($body, 'password');

        $user = $this->users->findByIdentification($identification);

        if (! $user || ! $user->checkPassword($password)) {
            throw new NotAuthenticatedException;
        }

        if ($this->twoFactorActive($user)) {
            $token = $this->retrieveTwoFactorTokenFrom(Arr::get($body, 'twoFactorToken'));

            if (! $this->isTokenActive($token, $user)) {
                throw new NotAuthenticatedException('two_factor_required');
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
