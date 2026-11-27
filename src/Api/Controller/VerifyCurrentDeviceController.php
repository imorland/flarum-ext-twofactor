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
use IanM\TwoFactor\Services\DeviceChangeGrant;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VerifyCurrentDeviceController implements RequestHandlerInterface
{
    use TwoFactorAuthenticationTrait;

    public function __construct(protected TotpInterface $totp, protected CodeVerificationThrottler $throttler, protected DeviceChangeGrant $grant)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        $token = $this->codeFrom($request);

        $twoFactor = TwoFactor::getForUser($actor);

        if (! $twoFactor->secret) {
            throw new ValidationException(['token' => 'Two-factor authentication is not enabled']);
        }

        if ($token !== '' && ! $this->throttler->attempt($actor, $request)) {
            throw new FloodingException();
        }

        if ($this->isTokenActive($token, $actor)) {
            $this->throttler->succeeded($actor, $request);

            return new JsonResponse([
                'success' => true,
                'changeToken' => $this->grant->issue($actor),
            ]);
        } else {
            throw new ValidationException(['token' => 'The token is invalid.']);
        }
    }
}
