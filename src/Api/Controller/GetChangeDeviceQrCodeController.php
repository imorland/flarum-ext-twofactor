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
use Flarum\User\User;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Services\QrCodeGenerator;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GetChangeDeviceQrCodeController implements RequestHandlerInterface
{
    public function __construct(
        protected TotpInterface $totp,
        protected QrCodeGenerator $qrCodeGenerator
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $routeParams = $request->getAttribute('routeParameters', []);
        $userId = Arr::get($routeParams, 'id');

        // Ensure the actor is the user or has permission to manage users
        if ($actor->id != $userId && !$actor->hasPermission('ianm-twofactor.manageOthers')) {
            throw new \Flarum\User\Exception\PermissionDeniedException();
        }

        $user = User::findOrFail($userId);
        $twoFactor = TwoFactor::getForUser($user);

        if (!$twoFactor->secret) {
            throw new ValidationException(['token' => 'Two-factor authentication is not enabled']);
        }

        // Generate a new secret for the new device
        $newSecret = $this->totp->createSecret();
        $twoFactor->setTempSecret($newSecret);

        // Set the label for the QR code
        $label = $user->username;

        // Generate the QR code
        $uri = $this->totp->getProvisioningUri($label, $newSecret);
        $svg = $this->qrCodeGenerator->generate($uri, true);

        return new JsonResponse([
            'svg' => $svg,
            'code' => $newSecret
        ]);
    }
}
