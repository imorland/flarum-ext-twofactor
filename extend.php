<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource\ForumResource;
use Flarum\Api\Resource\GroupResource;
use Flarum\Api\Resource\UserResource;
use Flarum\Audit\Extend\Audit;
use Flarum\Extend;
use Flarum\Gdpr\Extend\UserData;
use Flarum\Group\Event\Saving as GroupSaving;
use Flarum\Group\Group;
use Flarum\User\User;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\OAuth\TwoFactorOAuthCheck;
use IanM\TwoFactor\OAuth\TwoFactorOAuthListener;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Model(Group::class))
        ->cast('tfa_required', 'bool'),

    (new Extend\Model(User::class))
        ->hasOne('twoFactor', TwoFactor::class, 'user_id'),

    (new Extend\Routes('api'))
        ->get('/users/{id}/twofactor/qrcode', 'user.twofactor.get-qr', Api\Controller\ShowQrCodeController::class)
        ->post('/users/twofactor/verify', 'user.twofactor.verify', Api\Controller\VerifyTwoFactorController::class)
        ->delete('/users/{id}/twofactor/disable', 'user.twofactor.disable', Api\Controller\DisableTwoFactorController::class)
        ->post('/users/twofactor/verify-current', 'user.twofactor.verify-current', Api\Controller\VerifyCurrentDeviceController::class)
        ->get('/users/{id}/twofactor/change-device/qrcode', 'user.twofactor.change-device.qrcode', Api\Controller\GetChangeDeviceQrCodeController::class)
        ->post('/users/twofactor/change-device/verify', 'user.twofactor.change-device.verify', Api\Controller\VerifyNewDeviceController::class)
        ->remove('token')
        ->post('/token', 'token', Api\Controller\CreateTwoFactorTokenController::class)
        ->post('/ianm_twofactor_logo', 'ianm_twofactor.logo', Api\Controller\UploadLogoController::class)
        ->delete('/ianm_twofactor_logo', 'ianm_twofactor.logo.delete', Api\Controller\DeleteLogoController::class),

    (new Extend\Routes('forum'))
        ->remove('login')
        ->post('/login', 'login', Api\Controller\TwoFactorLogInController::class)
        ->remove('maintenance.login')
        ->get('/maintenance/login', 'maintenance.login', Api\Controller\TwoFactorLogInViewController::class)
        ->remove('savePassword')
        ->post('/reset', 'savePassword', Api\Controller\TwoFactorSavePasswordController::class)
        ->remove('resetPassword')
        ->get('/reset/{token}', 'resetPassword', Api\Controller\TwoFactorResetPasswordController::class),

    (new Extend\ServiceProvider())
        ->register(Provider\TwoFactorServiceProvider::class),

    (new Extend\ApiResource(UserResource::class))
        ->fields(Api\AddUserAttributes::class)
        ->endpoint([Endpoint\Show::class, Endpoint\Update::class], function (Endpoint\Show|Endpoint\Update $endpoint) {
            return $endpoint->addDefaultInclude(['twoFactor']);
        }),

    (new Extend\ApiResource(GroupResource::class))
        ->fields(Api\AddGroupAttributes::class),

    (new Extend\ApiResource(ForumResource::class))
        ->fields(Api\AddForumAttributes::class),

    (new Extend\Notification())
        ->type(Notification\TwoFactorStatusChangedBlueprint::class, ['email']),

    (new Extend\Event())
        ->subscribe(Listener\QueueNotificationJobs::class)
        ->listen(GroupSaving::class, Listener\SaveGroup2FASetting::class),

    (new Extend\View())
        ->namespace('ianm-two-factor', __DIR__.'/views'),

    (new Extend\Settings())
        ->default('ianm-twofactor.admin.settings.forum_logo_qr', true)
        ->default('ianm-twofactor.admin.settings.forum_logo_qr_width', 100)
        ->default('ianm-twofactor.kill_inactive_tokens', true)
        ->default('ianm-twofactor.kill_inactive_tokens_age_days', 30)
        ->default('ianm-twofactor.also_kill_developer_tokens', false),

    (new Extend\Console())
        ->command(Console\KillInactiveTokensCommand::class)
        ->command(Console\DisableTwoFactorCommand::class)
        ->schedule(Console\KillInactiveTokensCommand::class, Console\InactiveTokensSchedule::class),

    (new Extend\Conditional())
        ->whenExtensionEnabled('fof-oauth', fn () => [
            (new Extend\Middleware('forum'))
                ->add(TwoFactorOAuthCheck::class),

            (new Extend\Event())
                ->listen(\FoF\OAuth\Events\OAuthLoginSuccessful::class, TwoFactorOAuthListener::class),

            (new Extend\Routes('forum'))
                ->get('/twofactor/oauth/verify', 'twoFactor.oauth', Api\Controller\TwoFactorOAuthController::class)
                ->post('/twofactor/oauth/verify', 'twoFactor.oauth.verify', Api\Controller\TwoFactorOAuthVerifyController::class),

            (new Extend\Conditional())
                ->whenExtensionEnabled('sycho-private-facade', fn () => [
                    (new \SychO\PrivateFacade\Extend\FacadeExclusions())
                        ->addBackendRouteExclusion('twoFactor.oauth')
                        ->addBackendRouteExclusion('twoFactor.oauth.verify')
                ]),
        ])
        ->whenExtensionEnabled('flarum-gdpr', fn () => [
            (new UserData())
                ->addType(Data\TwoFactorData::class),
        ])
        ->whenExtensionEnabled('flarum-audit', fn () => [
            (new Audit())
                ->listen(Event\Enabled::class, 'user.two_factor_enabled', fn (Event\Enabled $event) => [
                    'user_id' => $event->user->id,
                ])
                ->listen(Event\Disabled::class, 'user.two_factor_disabled', fn (Event\Disabled $event) => [
                    'user_id' => $event->user->id,
                ])
                ->listen(Event\DeviceChanged::class, 'user.two_factor_device_changed', fn (Event\DeviceChanged $event) => [
                    'user_id' => $event->user->id,
                ])
                ->listen(Event\BackupCodeUsed::class, 'user.two_factor_backup_code_used', fn (Event\BackupCodeUsed $event) => [
                    'user_id' => $event->user->id,
                    'remaining' => $event->remaining,
                ])
                ->listen(Event\GroupRequirementChanged::class, 'group.two_factor_required', fn (Event\GroupRequirementChanged $event) => $event->required ? [
                    'group_id' => $event->group->id,
                    'name' => $event->group->name_singular,
                ] : null)
                ->listen(Event\GroupRequirementChanged::class, 'group.two_factor_not_required', fn (Event\GroupRequirementChanged $event) => $event->required ? null : [
                    'group_id' => $event->group->id,
                    'name' => $event->group->name_singular,
                ]),
        ]),

    new Extend\ApiResource(Api\Resource\TwoFactorResource::class),
];
