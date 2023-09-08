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

use Flarum\Http\RequestUtil;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\User\User;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Services\QrCodeGenerator;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ShowQrCodeController implements RequestHandlerInterface
{
    public function __construct(protected TotpInterface $totp, protected QrCodeGenerator $generator)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        $actor->assertRegistered();

        /** @var User $user */
        $user = User::find(Arr::get($request->getQueryParams(), 'id'));

        if ($user->id !== $actor->id) {
            throw new PermissionDeniedException('You cannot create a QR code for another user.');
        }

        $twoFactor = TwoFactor::getForUser($user);
        if (empty($twoFactor->secret)) {
            $twoFactor->secret = $this->totp->createSecret();
            $twoFactor->save();
        }

        $qrDataUri = $this->generator->generate(
            $this->totp->getProvisioningUri($user->display_name, $twoFactor->secret),
            true
        );

        return new JsonResponse([
            'svg' => $qrDataUri,
            'code' => $twoFactor->secret
        ]);
    }
}
