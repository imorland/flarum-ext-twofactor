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
 * Upgrades keep the "remember me" expiry this extension used to enforce, rather
 * than falling back to core's five years. First installs leave core's alone.
 */
return [
    'up' => function (Builder $schema) {
        $db = $schema->getConnection();
        $get = fn (string $key) => $db->table('settings')->where('key', $key)->value('value');
        $set = fn (string $key, string $value) => $db->table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);

        $upgrading = $db->table('two_factor')->exists()
            || $db->table('settings')->where('key', 'like', 'ianm-twofactor.%')->exists();

        $enabled = $get('ianm-twofactor.kill_inactive_tokens');

        if ($upgrading && ($enabled === null || (bool) $enabled)) {
            $days = $get('ianm-twofactor.kill_inactive_tokens_age_days');
            $lifetime = (is_numeric($days) && (int) $days > 0 ? (int) $days : 30) * 24 * 60 * 60;

            $current = $get('session.tokens.session_remember');

            // Zero means "never expires" in core.
            if (is_numeric($current) && (int) $current > 0) {
                $lifetime = min($lifetime, (int) $current);
            }

            $set('session.tokens.session_remember', (string) $lifetime);
        } elseif ($enabled !== null) {
            // An admin who turned the cleanup off didn't ask for developer tokens to go.
            $set('ianm-twofactor.also_kill_developer_tokens', '0');
        }

        // ianm-twofactor.kill_inactive_tokens is left in place for earlier releases, should the extension be downgraded.
    },

    'down' => function (Builder $schema) {
        // The previous settings can't be told apart from what an admin set in core since.
    },
];
