<?php

namespace IanM\TwoFactor\Api\Controller;

use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use IanM\TwoFactor\Contracts\TotpInterface;
use IanM\TwoFactor\Event\Enabled;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\Services\BackupCodeGenerator;
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
    
    public function __construct(protected TotpInterface $totp, protected BackupCodeGenerator $backupCodeGenerator, protected Dispatcher $events)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $token = Arr::get($request->getParsedBody(), 'token', '');
        $reVerify = (bool) Arr::get($request->getParsedBody(), 'reVerify', false);

        $twoFactor = TwoFactor::getForUser($actor);
        
        if ($this->isTokenActive($token, $actor)) {
            $backupCodes = [];
            if (!$reVerify) {
                $twoFactor->is_active = true;
                $twoFactor->save();

                $backupCodes = $this->backupCodeGenerator->generate($actor);

                $this->events->dispatch(new Enabled($actor, !empty($backupCodes)));
            }

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
