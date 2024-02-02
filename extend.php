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

use Blomstra\Gdpr\Extend\UserData;
use Flarum\Api\Controller\ShowUserController;
use Flarum\Api\Serializer\BasicUserSerializer;
use Flarum\Api\Serializer\CurrentUserSerializer;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Api\Serializer\GroupSerializer;
use Flarum\Extend;
use Flarum\Group\Event\Saving as GroupSaving;
use Flarum\Group\Group;
use Flarum\User\User;
use IanM\TwoFactor\Api\Serializer\TwoFactorSerializer;
use IanM\TwoFactor\Model\TwoFactor;
use IanM\TwoFactor\OAuth\TwoFactorOAuthCheck;
use SychO\PrivateFacade\Extend\FacadeExclusions;

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

    (new Extend\Routes('api'))
        ->get('/users/{id}/twofactor/qrcode', 'user.twofactor.get-qr', Api\Controller\ShowQrCodeController::class)
        ->post('/users/twofactor/verify', 'user.twofactor.verify', Api\Controller\VerifyTwoFactorController::class)
        ->delete('/users/{id}/twofactor/disable', 'user.twofactor.disable', Api\Controller\DisableTwoFactorController::class)
        ->remove('token')
        ->post('/token', 'token', Api\Controller\CreateTwoFactorTokenController::class)
        ->post('/ianm_twofactor_logo', 'ianm_twofactor.logo', Api\Controller\UploadLogoController::class)
        ->delete('/ianm_twofactor_logo', 'ianm_twofactor.logo.delete', Api\Controller\DeleteLogoController::class),

    (new Extend\Routes('forum'))
        ->remove('login')
        ->post('/login', 'login', Api\Controller\TwoFactorLogInController::class)
        ->remove('savePassword')
        ->post('/reset', 'savePassword', Api\Controller\TwoFactorSavePasswordController::class)
        ->remove('resetPassword')
        ->get('/reset/{token}', 'resetPassword', Api\Controller\TwoFactorResetPasswordController::class),

    (new Extend\ServiceProvider())
        ->register(Provider\TwoFactorServiceProvider::class),

    (new Extend\Model(User::class))
        ->hasOne('twoFactor', TwoFactor::class, 'user_id'),

    (new Extend\ApiSerializer(CurrentUserSerializer::class))
        ->attributes(Api\AddCurrentUserAttributes::class),

    (new Extend\ApiSerializer(BasicUserSerializer::class))
        ->attributes(Api\AddUserAttributes::class),

    (new Extend\ApiSerializer(GroupSerializer::class))
        ->attributes(Api\AddGroupAttributes::class),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attributes(Api\AddForumAttributes::class),

    (new Extend\ApiController(ShowUserController::class))
        ->addInclude('twoFactor'),

    (new Extend\ApiSerializer(CurrentUserSerializer::class))
        ->hasOne('twoFactor', TwoFactorSerializer::class),

    (new Extend\Notification())
        ->type(Notification\TwoFactorStatusChangedBlueprint::class, BasicUserSerializer::class, ['email']),

    (new Extend\Event())
        ->subscribe(Listener\QueueNotificationJobs::class)
        ->listen(GroupSaving::class, Listener\SaveGroup2FASetting::class),

    (new Extend\View())
        ->namespace('ianm-two-factor', __DIR__.'/views'),

    (new Extend\Settings())
        ->default('ianm-twofactor.admin.settings.forum_logo_qr', true)
        ->default('ianm-twofactor.admin.settings.forum_logo_qr_width', 100),

    (new Extend\Conditional())
        ->whenExtensionEnabled('fof-oauth', fn () => [
            (new \FoF\Extend\Extend\OAuthController())
                ->afterOAuthSuccess(TwoFactorOAuthCheck::class),

            (new Extend\Routes('forum'))
                ->get('/twofactor/oauth/verify', 'twoFactor.oauth', Api\Controller\TwoFactorOAuthController::class)
                ->post('/twofactor/oauth/verify', 'twoFactor.oauth.verify', Api\Controller\TwoFactorOAuthVerifyController::class),

            (new Extend\Conditional())
                // Not using `->whenExtensionEnabled` here so that we can support older versions of the facade extension (but without oauth support)
                ->when(class_exists(FacadeExclusions::class), fn () => [
                    (new FacadeExclusions())
                        ->addBackendRouteExclusion('twoFactor.oauth')
                        ->addBackendRouteExclusion('twoFactor.oauth.verify')
                ])
        ])
        ->whenExtensionEnabled('blomstra-gdpr', fn () => [
            (new UserData())
                ->addType(Data\TwoFactorData::class),
        ]),
];
