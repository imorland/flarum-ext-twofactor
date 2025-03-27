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
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Services\BackupCodeGenerator;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VerifyNewDeviceController implements RequestHandlerInterface
{
    public function __construct(
        protected TotpInterface $totp,
        protected BackupCodeGenerator $backupCodeGenerator
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $token = Arr::get($request->getParsedBody(), 'token', '');

        $twoFactor = TwoFactor::getForUser($actor);
        $tempSecret = $twoFactor->getTempSecret();

        if (!$tempSecret) {
            throw new ValidationException(['token' => 'No pending device change found']);
        }

        if ($this->totp->verify($tempSecret, $token, $actor)) {
            // Token is valid, commit the new secret
            $twoFactor->commitTempSecret();

            // Generate new backup codes
            $backupCodes = $this->backupCodeGenerator->generate($actor);

            return new JsonResponse([
                'success' => true,
                'backupCodes' => $backupCodes
            ]);
        } else {
            // Token is invalid
            throw new ValidationException(['token' => 'The token is invalid.']);
        }
    }
}
