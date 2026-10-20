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

use Flarum\Api\Client;
use Flarum\Extension\ExtensionManager;
use Flarum\Forum\Controller\LogInController;
use Flarum\Forum\LogInValidator;
use Flarum\Http\AccessToken;
use Flarum\Http\RememberAccessToken;
use Flarum\Http\Rememberer;
use Flarum\Http\RequestUtil;
use Flarum\Http\SessionAuthenticator;
use Flarum\Http\UrlGenerator;
use Flarum\Locale\TranslatorInterface;
use Flarum\User\Event\LoggedIn;
use Flarum\User\UserRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class TwoFactorLogInController extends LogInController
{
    public function __construct(
        UserRepository $users,
        Client $apiClient,
        SessionAuthenticator $authenticator,
        Dispatcher $events,
        Rememberer $rememberer,
        LogInValidator $validator,
        UrlGenerator $url,
        TranslatorInterface $translator,
        protected ExtensionManager $extensions
    ) {
        parent::__construct($users, $apiClient, $authenticator, $events, $rememberer, $validator, $url, $translator);
    }

    public function handle(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();

        // HTML requests come from the server-rendered maintenance login page,
        // which needs errors in the session and a redirect, not a JSON response.
        $isHtmlRequest = RequestUtil::isHtmlRequest($request);

        if ($isHtmlRequest) {
            $validator = $this->validator->basic()->prepare($body)->validator();

            if (! $validator->passes()) {
                $request->getAttribute('session')->put('errors', $validator->errors());

                return $this->redirectToLogInPage();
            }
        } elseif (! $this->isTurnstileEnabled() && empty(Arr::get($body, 'twoFactorToken'))) {
            $this->validator->assertValid($body);
        }

        $response = $this->apiClient->withParentRequest($request)
            ->withBody($body)
            ->post('/token');

        $token = null;

        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody());

            $token = AccessToken::findValid($data->token);

            $session = $request->getAttribute('session');
            $this->authenticator->logIn($session, $token);

            $this->events->dispatch(new LoggedIn($this->users->findOrFail($data->userId), $token));
        } elseif ($isHtmlRequest) {
            $request->getAttribute('session')->put('errors', $this->htmlErrorsFor($response));
        }

        if ($isHtmlRequest) {
            $response = $this->redirectToLogInPage();
        }

        if ($token instanceof RememberAccessToken) {
            $response = $this->rememberer->remember($response, $token);
        }

        return $response;
    }

    protected function htmlErrorsFor(ResponseInterface $response): MessageBag
    {
        return match ($response->getStatusCode()) {
            401 => new MessageBag(['identification' => $this->translator->trans('core.views.log_in.invalid_login_message')]),
            429 => new MessageBag(['identification' => $this->translator->trans('core.views.log_in.too_many_attempts_message')]),
            default => $this->validationErrorsFor($response),
        };
    }

    protected function validationErrorsFor(ResponseInterface $response): MessageBag
    {
        $errors = new MessageBag();

        foreach (Arr::get(json_decode((string) $response->getBody(), true) ?? [], 'errors', []) as $error) {
            $detail = Arr::get($error, 'detail');

            $errors->add('twoFactorToken', match ($detail) {
                'two_factor_required' => $this->translator->trans('ianm-twofactor.views.log_in.two_factor_required_message'),
                'two_factor_incorrect' => $this->translator->trans('ianm-twofactor.views.log_in.two_factor_incorrect_message'),
                default => $detail ?? $this->translator->trans('core.views.log_in.invalid_login_message'),
            });
        }

        return $errors;
    }

    protected function redirectToLogInPage(): RedirectResponse
    {
        return new RedirectResponse($this->url->to('forum')->route('maintenance.login'));
    }

    protected function isTurnstileEnabled(): bool
    {
        return $this->extensions->isEnabled('flectar-turnstile');
    }
}
