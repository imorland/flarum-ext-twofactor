<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Search;

use Flarum\Search\Database\DatabaseSearchState;
use Flarum\Search\Filter\FilterInterface;
use Flarum\Search\SearchState;
use Illuminate\Database\Query\Builder;

/**
 * @implements FilterInterface<DatabaseSearchState>
 */
class TwoFactorFilter implements FilterInterface
{
    public function getFilterKey(): string
    {
        return 'twofactor';
    }

    public function filter(SearchState $state, string|array $value, bool $negate): void
    {
        if (! $state->getActor()->can('ianm-twofactor.seeTwoFactorStatus')) {
            return;
        }

        $query = $state->getQuery();
        $users = $query->getModel()->getTable();

        $query->whereExists(function (Builder $query) use ($users) {
            $query->selectRaw('1')
                ->from('two_factor')
                ->whereColumn('two_factor.user_id', "$users.id")
                ->where('two_factor.is_active', true);
        }, 'and', $negate);
    }
}
