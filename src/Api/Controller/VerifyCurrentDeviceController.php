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
use Flarum\Http\RequestUtil;
use Flarum\Post\Exception\FloodingException;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Services\CodeVerificationThrottler;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VerifyCurrentDeviceController implements RequestHandlerInterface
{
    use TwoFactorAuthenticationTrait;

    public function __construct(protected TotpInterface $totp, protected CodeVerificationThrottler $throttler)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        $token = Arr::get($request->getParsedBody(), 'token', '');

        $twoFactor = TwoFactor::getForUser($actor);

        if (! $twoFactor->secret) {
            throw new ValidationException(['token' => 'Two-factor authentication is not enabled']);
        }

        if ($this->throttler->tooManyAttempts($actor, $request)) {
            throw new FloodingException();
        }

        if ($this->isTokenActive($token, $actor)) {
            return new JsonResponse([
                'success' => true
            ]);
        } else {
            if ((string) $token !== '') {
                $this->throttler->recordFailure($actor, $request);
            }

            throw new ValidationException(['token' => 'The token is invalid.']);
        }
    }
}
