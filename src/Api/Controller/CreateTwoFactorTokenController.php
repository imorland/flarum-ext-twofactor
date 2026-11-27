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
use Flarum\Post\Exception\FloodingException;
use Flarum\User\Exception\NotAuthenticatedException;
use Flarum\User\Throttler\LoginThrottler;
use Flarum\User\UserRepository;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Services\CodeVerificationThrottler;
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
        protected EventDispatcher $events,
        protected LoginThrottler $throttler,
        protected CodeVerificationThrottler $codeThrottler
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();

        $identification = Arr::get($body, 'identification');
        $password = Arr::get($body, 'password');

        $user = $identification
            ? $this->users->findByIdentification($identification)
            : null;

        if (! $user || ! $user->checkPassword($password)) {
            throw new NotAuthenticatedException();
        }

        if ($this->twoFactorActive($user)) {
            $token = $this->retrieveTwoFactorTokenFrom(Arr::get($body, 'twoFactorToken'));

            if (! $token) {
                throw new ValidationException(['twoFactorToken' => 'two_factor_required']);
            }

            // Core's login throttle has no per-account limit, since anyone can fail a
            // password; only someone holding this password can get to the code.
            if (! $this->codeThrottler->attempt($user, $request)) {
                throw new FloodingException();
            }

            if (! $this->isTokenActive($token, $user)) {
                // Core's RecordFailedLogins counts only NotAuthenticatedException,
                // which would lose the reason the login modal needs to show.
                if (! $request->getAttribute('bypassThrottling')) {
                    $this->throttler->recordFailure($request);
                }

                throw new ValidationException(['twoFactorToken' => 'two_factor_incorrect']);
            }

            $this->codeThrottler->succeeded($user, $request);
        }

        if (Arr::get($body, 'remember')) {
            $token = RememberAccessToken::generate($user->id);
        } else {
            $token = SessionAccessToken::generate($user->id);
        }

        $token->touch(null, $request);

        return new JsonResponse([
            'token' => $token->token,
            'userId' => $user->id
        ]);
    }
}
