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
use Flarum\User\Exception\PermissionDeniedException;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Event\DeviceChanged;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Services\BackupCodeGenerator;
use IanM\TwoFactor\Services\CodeVerificationThrottler;
use IanM\TwoFactor\Services\DeviceChangeGrant;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VerifyNewDeviceController implements RequestHandlerInterface
{
    public function __construct(
        protected TotpInterface $totp,
        protected BackupCodeGenerator $backupCodeGenerator,
        protected Dispatcher $events,
        protected CodeVerificationThrottler $throttler,
        protected DeviceChangeGrant $grant
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        $token = Arr::get($request->getParsedBody(), 'token', '');

        if (! $this->grant->check($actor, Arr::get($request->getParsedBody(), 'changeToken'))) {
            throw new PermissionDeniedException();
        }

        $twoFactor = TwoFactor::getForUser($actor);
        $tempSecret = $twoFactor->getTempSecret();

        if (! $tempSecret) {
            throw new ValidationException(['token' => 'No pending device change found']);
        }

        if ((string) $token !== '' && ! $this->throttler->attempt($actor, $request)) {
            throw new FloodingException();
        }

        if ($this->totp->verify($tempSecret, $token, $actor)) {
            $this->throttler->succeeded($actor, $request);

            // Token is valid, commit the new secret
            $twoFactor->commitTempSecret();
            $this->grant->revoke($actor);

            // Generate new backup codes
            $backupCodes = $this->backupCodeGenerator->generate($actor);

            // Dispatch device changed event for notification
            $this->events->dispatch(new DeviceChanged($actor));

            return new JsonResponse([
                'success' => true,
                'backupCodes' => $backupCodes
            ]);
        } else {
            throw new ValidationException(['token' => 'The token is invalid.']);
        }
    }
}
