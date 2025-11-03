<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Api\Controller;

use Flarum\Api\Controller\UploadImageController;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory;
use Intervention\Image\Constraint;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadLogoController extends UploadImageController
{
    protected $filePathSettingKey = 'ianm_twofactor_logo_path';

    protected $filenamePrefix = 'ianm_twofactor_logo';

    public function __construct(
        SettingsRepositoryInterface $settings,
        Factory $filesystemFactory,
        protected ImageManager $imageManager
    ) {
        parent::__construct($settings, $filesystemFactory);
    }

    protected function makeImage(UploadedFileInterface $file): EncodedImageInterface|StreamInterface
    {
        /**
         * @TODO: confirm if this still works with intervention/image v3
         *        see: https://image.intervention.io/v3/introduction/upgrade
         */
        $encodedImage = $this->imageManager
            ->make($file->getStream()->getMetadata('uri'))
            ->heighten(60, fn (Constraint $constraint) => $constraint->upsize())->encode('png');

        return $encodedImage;
    }
}
