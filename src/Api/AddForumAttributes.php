<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Api;

use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory;

class AddForumAttributes
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    protected $assetsFilesystem;

    public function __construct(Factory $filesystemFactory, SettingsRepositoryInterface $settings)
    {
        $this->assetsFilesystem = $filesystemFactory->disk('flarum-assets');
        $this->settings = $settings;
    }

    public function __invoke(ForumSerializer $serializer, $object, array $attributes): array
    {
        $attributes['ianm_twofactor_logoUrl'] = $this->getLogoUrl();

        return $attributes;
    }

    protected function getLogoUrl(): ?string
    {
        $logoPath = $this->settings->get('ianm_twofactor_logo_path');

        return $logoPath ? $this->getAssetUrl($logoPath) : null;
    }

    public function getAssetUrl(string $assetPath): string
    {
        return $this->assetsFilesystem->url($assetPath);
    }
}
