<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Builder;

/*
 * Inactive session and "remember me" tokens used to be deleted here, by default
 * after 30 days. Core now expires them itself (#4918), so carry that expiry over
 * to core's "remember me" lifetime rather than leaving it at core's five years.
 * Session tokens already expire within hours in core.
 */
return [
    'up' => function (Builder $schema) {
        $db = $schema->getConnection();
        $get = fn (string $key) => $db->table('settings')->where('key', $key)->value('value');
        $set = fn (string $key, string $value) => $db->table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);

        $enabled = $get('ianm-twofactor.kill_inactive_tokens');

        if ($enabled === null || (bool) $enabled) {
            $days = $get('ianm-twofactor.kill_inactive_tokens_age_days');
            $lifetime = (is_numeric($days) && (int) $days > 0 ? (int) $days : 30) * 24 * 60 * 60;

            $current = $get('session.tokens.session_remember');

            // Zero means "never expires" in core.
            if (is_numeric($current) && (int) $current > 0) {
                $lifetime = min($lifetime, (int) $current);
            }

            $set('session.tokens.session_remember', (string) $lifetime);
        } else {
            // The developer token cleanup only ran while the whole cleanup was on.
            $set('ianm-twofactor.also_kill_developer_tokens', '0');
        }

        $db->table('settings')->where('key', 'ianm-twofactor.kill_inactive_tokens')->delete();
    },

    'down' => function (Builder $schema) {
        // The previous settings can't be told apart from what an admin set in core since.
    },
];
