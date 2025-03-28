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

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\Group\Group;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $secret
 * @property string $backup_codes
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $temp_secret
 * @property \Carbon\Carbon|null $temp_secret_created_at
 */
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
    protected $fillable = ['user_id', 'secret', 'backup_codes', 'is_active', 'temp_secret', 'temp_secret_created_at'];  // Add other fields as necessary

    public $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'temp_secret_created_at' => 'datetime',
    ];

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

    /**
     * Set a temporary secret for device change.
     *
     * @param string $secret
     * @return void
     */
    public function setTempSecret(string $secret): void
    {
        $this->temp_secret = $secret;
        $this->temp_secret_created_at = Carbon::now();
        $this->save();
    }

    /**
     * Get the temporary secret.
     *
     * @return string|null
     */
    public function getTempSecret(): ?string
    {
        // If temp secret is older than 10 minutes, consider it expired
        if ($this->temp_secret_created_at && Carbon::now()->subMinutes(10)->isAfter($this->temp_secret_created_at)) {
            $this->temp_secret = null;
            $this->temp_secret_created_at = null;
            $this->save();

            return null;
        }

        return $this->temp_secret;
    }

    /**
     * Commit the temporary secret as the new primary secret.
     *
     * @return void
     */
    public function commitTempSecret(): void
    {
        if (! $this->temp_secret) {
            return;
        }

        $this->secret = $this->temp_secret;
        $this->temp_secret = null;
        $this->temp_secret_created_at = null;
        $this->save();
    }

    /**
     * Clear the temporary secret.
     *
     * @return void
     */
    public function clearTempSecret(): void
    {
        $this->temp_secret = null;
        $this->temp_secret_created_at = null;
        $this->save();
    }
}
