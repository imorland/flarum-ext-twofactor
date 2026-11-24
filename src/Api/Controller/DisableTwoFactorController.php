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
use Flarum\User\User;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Event\Disabled;
use IanM\TwoFactor\Services\CodeVerificationThrottler;
use IanM\TwoFactor\Services\TwoFactorRestrictor;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DisableTwoFactorController implements RequestHandlerInterface
{
    use TwoFactorAuthenticationTrait;

    public function __construct(public Dispatcher $events, public TwoFactorRestrictor $restrictor, protected TotpInterface $totp, protected CodeVerificationThrottler $throttler)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     *
     * @throws PermissionDeniedException
     * @throws ModelNotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered();

        $user = User::findOrFail(Arr::get($request->getQueryParams(), 'id'));

        if (! $this->restrictor->canDisable2FAFor($actor, $user)) {
            throw new PermissionDeniedException('You are not allowed to disable 2FA for this user.');
        }

        if (! $this->twoFactorActive($user)) {
            throw new ModelNotFoundException();
        }

        // Someone holding only a session must not be able to turn it off. Recovering
        // another user's account (manageOthers) has no code to give.
        if ($actor->id === $user->id) {
            $this->assertCurrentCode($user, $request);
        }

        $user->twoFactor->delete();

        $this->events->dispatch(new Disabled($user, $actor));

        return new EmptyResponse(204);
    }

    protected function assertCurrentCode(User $user, ServerRequestInterface $request): void
    {
        $code = (string) Arr::get((array) $request->getParsedBody(), 'token', '');

        if ($code === '') {
            throw new ValidationException(['token' => 'two_factor_required']);
        }

        if (! $this->throttler->attempt($user, $request)) {
            throw new FloodingException();
        }

        if (! $this->isTokenActive($code, $user)) {
            throw new ValidationException(['token' => 'two_factor_incorrect']);
        }

        $this->throttler->succeeded($user, $request);
    }
}
