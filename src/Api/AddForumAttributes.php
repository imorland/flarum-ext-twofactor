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

use Flarum\Api\Schema;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Factory;

class AddForumAttributes
{
    /**
     * @var Cloud
     */
    protected $assetsFilesystem;

    public function __construct(Factory $filesystemFactory, protected SettingsRepositoryInterface $settings)
    {
        $this->assetsFilesystem = $filesystemFactory->disk('flarum-assets');
    }

    public function __invoke(): array
    {
        return [
            Schema\Str::make('ianm_twofactor_logoUrl')
                ->nullable()
                ->get(fn () => $this->getLogoUrl())
                ->visible(fn ($_, $context) => $context->getActor()->can('administrate')),
        ];
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
