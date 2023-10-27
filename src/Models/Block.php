<?php

namespace TimGavin\LaravelBlock\Models;

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
     *
     * @return BelongsTo
     */
    public function blocking(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'blocking_id');
    }

    /**
     * Returns who is blocking a user.
     *
     * @return BelongsTo
     */
    public function blockers(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }
}
