<?php

namespace IanM\TwoFactor\Api\Controller;

use Flarum\Http\Controller\AbstractHtmlController;
use Illuminate\Contracts\View\Factory;
use Psr\Http\Message\ServerRequestInterface;

class TwoFactorOAuthController extends AbstractHtmlController
{
    public function __construct(protected Factory $view)
    {
    }
    
    protected function render(ServerRequestInterface $request)
    {
        return $this->view->make('ianm-two-factor::oauth.verify')
            ->with('csrfToken', $request->getAttribute('session')->token());
    }
}
