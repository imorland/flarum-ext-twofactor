<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Notification;

use Carbon\Carbon;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Notification\MailableInterface;
use Flarum\User\User;
use IanM\TwoFactor\Event\Disabled;
use IanM\TwoFactor\Event\Enabled;
use Symfony\Contracts\Translation\TranslatorInterface;

class TwoFactorStatusChangedBlueprint implements BlueprintInterface, MailableInterface
{
    public function __construct(public Enabled|Disabled $event)
    {
    }

    public function getFromUser()
    {
        return $this->event instanceof Enabled ? $this->event->user : $this->event->actor;
    }

    public function getSubject()
    {
        return $this->event->user;
    }

    public static function getType()
    {
        return '2faStatusChanged';
    }

    public function type(): string
    {
        return $this->event instanceof Enabled ? 'enabled' : 'disabled';
    }

    public static function getSubjectModel()
    {
        return User::class;
    }

    public function getData()
    {
        return [
            'generated' => Carbon::now()->toIso8601String(),
        ];
    }

    public function getEmailView()
    {
        return [
            'text' => 'ianm-two-factor::email.status_changed'
        ];
    }

    public function getEmailSubject(TranslatorInterface $translator): string
    {
        return $translator->trans('ianm-twofactor.email.subject.status_changed', [
            '{type}' => $this->type(),
        ]);
    }
}
