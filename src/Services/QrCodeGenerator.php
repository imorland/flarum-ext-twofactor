<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Factory;
use Psr\Log\LoggerInterface;

class QrCodeGenerator
{
    protected Cloud $assetsFilesystem;

    public function __construct(protected SettingsRepositoryInterface $settings, Factory $filesystemFactory, protected LoggerInterface $logger)
    {
        $this->assetsFilesystem = $filesystemFactory->disk('flarum-assets');
    }

    /**
     * Generate a QR code SVG from the given text.
     *
     * @param string $text
     * @param bool $asDataUri Whether to return a data URI.
     * @return string
     */
    public function generate(string $text, bool $asDataUri = false): string
    {
        $builder = $this->buildQrOptions($text);
        $builder = $this->addLogoToBuilder($builder);

        try {
            $result = $builder->build();
        } catch (\Exception $e) {
            $this->logger->error('[ianm/twofactor] Could not add logo to QR code: '.$e->getMessage());
            $builder = $this->buildQrOptions($text);
            $result = $builder->build();
        }

        if ($asDataUri) {
            return $result->getDataUri();
        }

        return $result->getString();
    }

    protected function buildQrOptions(string $text): BuilderInterface
    {
        return Builder::create()
        ->writer(new Writer\PngWriter())
        ->writerOptions([])
        ->data($text)
        ->encoding(new Encoding('UTF-8'))
        ->errorCorrectionLevel(new ErrorCorrectionLevel\ErrorCorrectionLevelQuartile())
        ->size(300)
        ->margin(10)
        ->roundBlockSizeMode(new RoundBlockSizeMode\RoundBlockSizeModeMargin())
        ->validateResult(false)
        ->backgroundColor(new Color(255, 255, 255, 1));
    }

    protected function addLogoToBuilder(BuilderInterface $builder): BuilderInterface
    {
        if ($this->settings->get('ianm-twofactor.admin.settings.forum_logo_qr') && $this->getLogoUrl()) {
            $builder
                ->logoPath($this->getLogoUrl())
                ->logoResizeToWidth($this->settings->get('ianm-twofactor.admin.settings.forum_logo_qr_width') ?? 100)
                ->logoPunchoutBackground(true);
        }

        return $builder;
    }

    protected function getLogoUrl(): ?string
    {
        $logoPath = $this->settings->get('ianm_twofactor_logo_path') ?? $this->settings->get('logo_path');

        return $logoPath ? $this->getAssetUrl($logoPath) : null;
    }

    public function getAssetUrl($assetPath): string
    {
        return $this->assetsFilesystem->url($assetPath);
    }
}
