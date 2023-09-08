<?php

namespace IanM\TwoFactor\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\SvgWriter;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Contracts\Filesystem\Factory;

class QrCodeGenerator
{
    protected Cloud $assetsFilesystem;
    
    public function __construct(protected SettingsRepositoryInterface $settings, Factory $filesystemFactory)
    {
        $this->assetsFilesystem = $filesystemFactory->disk('flarum-assets');
    }

    public function generate(string $text, bool $asDataUri = false): string
    {
        $result = Builder::create()
            ->writer(new SvgWriter())
            ->writerOptions([])
            ->data($text)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            //->logoPath($this->getLogoUrl())
            //->logoResizeToWidth(100)
            //->logoPunchoutBackground(true)
            //->labelText('This is the label')
            //->labelFont(new NotoSans(20))
            //->labelAlignment(new LabelAlignmentCenter())
            ->validateResult(false)
            ->build();


        if ($asDataUri) {
            return $result->getDataUri();
        }

        return $result->getString();
    }

    protected function getLogoUrl(): ?string
    {
        $logoPath = $this->settings->get('logo_path');

        return $logoPath ? $this->getAssetUrl($logoPath) : null;
    }

    public function getAssetUrl($assetPath): string
    {
        return $this->assetsFilesystem->url($assetPath);
    }
}
