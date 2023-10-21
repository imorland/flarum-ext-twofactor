<?php

/*
 * This file is part of ianm/twofactor.
 *
 * Copyright (c) 2023 IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\TwoFactor\Model;

use DateTime;
use Flarum\Database\AbstractModel;
use Flarum\Group\Group;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactor extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'two_factor';  // Replace with your actual table name

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['user_id', 'secret', 'backup_codes', 'is_active'];  // Add other fields as necessary

    /**
     * Get the user that owns this 2FA entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retrieve the TwoFactor instance for a given user or create a new one if it doesn't exist.
     *
     * @param User $user
     * @return TwoFactor
     */
    public static function getForUser(User $user): TwoFactor
    {
        return self::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * The `Group` IDs that should not have their 2FA status changed.
     *
     * @return array
     */
    public static function guardedGroups(): array
    {
        return [Group::ADMINISTRATOR_ID, Group::GUEST_ID];
    }
}
