<?php

namespace IanM\TwoFactor\Api\Resource;

use Flarum\Api\Context;
use Flarum\Api\Endpoint;
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

            // Schema\Relationship\ToOne::make('user')
            //     ->includable()
            //     // ->inverse('?') // the inverse relationship name if any.
            //     ->type('users'), // the serialized type of this relation (type of the relation model's API resource).
        ];
    }

    public function sorts(): array
    {
        return [
            // SortColumn::make('createdAt'),
        ];
    }
}
