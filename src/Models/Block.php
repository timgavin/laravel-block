<?php

namespace TimGavin\LaravelBlock\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    use HasFactory;

    protected $table = 'blocks';

    protected $fillable = [
        'user_id',
        'blocking_id',
    ];

    /**
     * Returns who a user is blocking.
     */
    public function blocking(): BelongsTo
    {
        $userModel = config('laravel-block.user_model') ?? config('auth.providers.users.model');

        return $this->belongsTo($userModel, 'blocking_id');
    }

    /**
     * Returns who is blocking a user.
     */
    public function user(): BelongsTo
    {
        $userModel = config('laravel-block.user_model') ?? config('auth.providers.users.model');

        return $this->belongsTo($userModel, 'user_id');
    }

    /**
     * Alias for user() for backwards compatibility.
     */
    public function blockers(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Scope to get blocks where a user is blocking others.
     */
    public function scopeWhereUserBlocks(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get blocks where a user is being blocked.
     */
    public function scopeWhereUserIsBlockedBy(Builder $query, int $userId): Builder
    {
        return $query->where('blocking_id', $userId);
    }

    /**
     * Scope to get blocks involving a specific user (either direction).
     */
    public function scopeInvolvingUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)
            ->orWhere('blocking_id', $userId);
    }
}
