<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Api\Serializer;

use Flarum\Api\Serializer\AbstractSerializer;
use Flarum\Api\Serializer\BasicUserSerializer;
use IanM\TwoFactor\Model\TwoFactor;
use InvalidArgumentException;
use Tobscure\JsonApi\Relationship;

class TwoFactorSerializer extends AbstractSerializer
{
    protected $type = 'twofactor';

    protected function getDefaultAttributes($model): array
    {
        if (! ($model instanceof TwoFactor)) {
            throw new InvalidArgumentException(
                get_class($this).' can only serialize instances of '.TwoFactor::class
            );
        }

        return [
            'is_active' => $model->is_active,
            'createdAt' => $this->formatDate($model->created_at),
            'updatedAt' => $this->formatDate($model->updated_at),
        ];
    }

    protected function user(TwoFactor $twoFactor): ?Relationship
    {
        return $this->hasOne($twoFactor, BasicUserSerializer::class, 'user_id');
    }
}
