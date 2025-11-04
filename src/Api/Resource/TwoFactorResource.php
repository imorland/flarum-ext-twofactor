<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Api\Resource;

use Flarum\Api\Resource;
use Flarum\Api\Schema;
use Flarum\Api\Sort\SortColumn;
use IanM\TwoFactor\Model\TwoFactor;
use Illuminate\Database\Eloquent\Builder;
use Tobyz\JsonApiServer\Context as OriginalContext;

/**
 * @extends Resource\AbstractDatabaseResource<TwoFactor>
 */
class TwoFactorResource extends Resource\AbstractDatabaseResource
{
    public function type(): string
    {
        return 'two-factor';
    }

    public function model(): string
    {
        return TwoFactor::class;
    }

    public function scope(Builder $query, OriginalContext $context): void
    {
        $query->whereVisibleTo($context->getActor());
    }

    public function endpoints(): array
    {
        return [
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Boolean::make('isActive'),

            Schema\DateTime::make('createdAt'),
            Schema\DateTime::make('updatedAt'),

            Schema\Relationship\ToOne::make('user')
                ->includable()
                ->type('users'),
        ];
    }

    public function sorts(): array
    {
        return [
            // SortColumn::make('createdAt'),
        ];
    }
}
