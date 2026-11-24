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
use IanM\TwoFactor\Event\Enabled;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Services\BackupCodeGenerator;
use IanM\TwoFactor\Services\CodeVerificationThrottler;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VerifyTwoFactorController implements RequestHandlerInterface
{
    use TwoFactorAuthenticationTrait;

    public function __construct(protected TotpInterface $totp, protected BackupCodeGenerator $backupCodeGenerator, protected Dispatcher $events, protected CodeVerificationThrottler $throttler)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        $token = $this->codeFrom($request);
        $reVerify = (bool) Arr::get($request->getParsedBody(), 'reVerify', false);

        $twoFactor = TwoFactor::getForUser($actor);

        if (! $twoFactor->secret) {
            throw new ValidationException(['token' => 'Two-factor authentication has not been set up']);
        }

        // Setting up must not double as a way to replace the backup codes.
        if ($twoFactor->is_active && ! $reVerify) {
            throw new ValidationException(['token' => 'Two-factor authentication is already enabled']);
        }

        if ($token !== '' && ! $this->throttler->attempt($actor, $request)) {
            throw new FloodingException();
        }

        if ($this->isTokenActive($token, $actor)) {
            $this->throttler->succeeded($actor, $request);

            $backupCodes = [];
            if (! $reVerify) {
                $twoFactor->is_active = true;
                $twoFactor->save();

                $backupCodes = $this->backupCodeGenerator->generate($actor);

                $this->events->dispatch(new Enabled($actor, ! empty($backupCodes)));
            }

            return new JsonResponse([
                'success' => true,
                'backupCodes' => $backupCodes
            ]);
        } else {
            throw new ValidationException(['token' => 'The token is invalid.']);
        }
    }
}
