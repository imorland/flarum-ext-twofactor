<?php

namespace IanM\TwoFactor\Api\Controller;

use Flarum\Forum\Controller\SavePasswordController;
use Flarum\Http\SessionAccessToken;
use Flarum\Http\SessionAuthenticator;
use Flarum\Http\UrlGenerator;
use Flarum\User\PasswordToken;
use Flarum\User\UserValidator;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Trait\TwoFactorAuthenticationTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class TwoFactorSavePasswordController extends SavePasswordController
{
    use TwoFactorAuthenticationTrait;
    
    public function __construct(protected TotpInterface $totp, UrlGenerator $url, SessionAuthenticator $authenticator, UserValidator $validator, Factory $validatorFactory, Dispatcher $events)
    {
        parent::__construct($url, $authenticator, $validator, $validatorFactory, $events);
    }

    public function handle(Request $request): ResponseInterface
    {
        $input = $request->getParsedBody();

        $token = PasswordToken::findOrFail(Arr::get($input, 'passwordToken'));

        $password = Arr::get($input, 'password');

        try {
            // todo: probably shouldn't use the user validator for this,
            // passwords should be validated separately
            $this->validator->assertValid(compact('password'));

            $validator = $this->validatorFactory->make($input, ['password' => 'required|confirmed']);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        } catch (ValidationException $e) {
            $request->getAttribute('session')->put('errors', new MessageBag($e->errors()));

            // @todo: must return a 422 instead, look into renderable exceptions.
            return new RedirectResponse($this->url->to('forum')->route('resetPassword', ['token' => $token->token]));
        }

        $twoFactorToken = $this->retrieveTwoFactorTokenFrom(Arr::get($input, 'twoFactorToken'));

        // Check if the user has 2FA enabled
        if ($this->twoFactorActive($token->user)) {
            if (!$this->isTokenActive($twoFactorToken, $token->user)) {
                $request->getAttribute('session')->put('errors', new MessageBag(['twoFactorToken' => 'Invalid 2FA token']));

                return new RedirectResponse($this->url->to('forum')->route('resetPassword', ['token' => $token->token]));
            }
        }

        $token->user->changePassword($password);
        $token->user->save();

        $this->dispatchEventsFor($token->user);

        $session = $request->getAttribute('session');
        $accessToken = SessionAccessToken::generate($token->user->id);
        $this->authenticator->logIn($session, $accessToken);

        return new RedirectResponse($this->url->to('forum')->base());
    }
}