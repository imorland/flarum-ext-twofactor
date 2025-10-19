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

use Flarum\Notification\AlertableInterface;
use Carbon\Carbon;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Notification\MailableInterface;
use Flarum\User\User;
use IanM\TwoFactor\Event\Disabled;
use IanM\TwoFactor\Event\Enabled;
use Symfony\Contracts\Translation\TranslatorInterface;

class TwoFactorStatusChangedBlueprint implements BlueprintInterface, MailableInterface, AlertableInterface
{
    public function __construct(public Enabled|Disabled $event)
    {
    }

    public function getFromUser(): ?\Flarum\User\User
    {
        return $this->event instanceof Enabled ? $this->event->user : $this->event->actor;
    }

    public function getSubject(): ?\Flarum\Database\AbstractModel
    {
        return $this->event->user;
    }

    public static function getType(): string
    {
        return '2faStatusChanged';
    }

    public function type(): string
    {
        return $this->event instanceof Enabled ? 'enabled' : 'disabled';
    }

    public static function getSubjectModel(): string
    {
        return User::class;
    }

    public function getData(): mixed
    {
        return [
            'generated' => Carbon::now()->toIso8601String(),
        ];
    }

    public function getEmailViews(): array
    {
        return [
            'text' => 'ianm-two-factor::email.plain.status_changed', 'html' => 'ianm-two-factor::email.html.status_changed'
        ];
    }

    public function getEmailSubject(\Flarum\Locale\TranslatorInterface $translator): string
    {
        return $translator->trans('ianm-twofactor.email.subject.status_changed', [
            '{type}' => $translator->trans('ianm-twofactor.email.status_type.'.$this->type()),
        ]);
    }
}
