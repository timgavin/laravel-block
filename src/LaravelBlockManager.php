<?php

namespace TimGavin\LaravelBlock;

use TimGavin\LaravelBlock\Models\Block;

class LaravelBlockManager
{
    /**
     * Get the Block model class.
     */
    public function model(): string
    {
        return Block::class;
    }

    /**
     * Get all blocks for a user.
     */
    public function getBlocksFor(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Block::where('user_id', $userId)->get();
    }

    /**
     * Get all blockers for a user.
     */
    public function getBlockersFor(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Block::where('blocking_id', $userId)->get();
    }

    /**
     * Check if a block relationship exists.
     */
    public function exists(int $userId, int $blockingId): bool
    {
        return Block::where('user_id', $userId)
            ->where('blocking_id', $blockingId)
            ->exists();
    }

    /**
     * Get the configured cache duration.
     */
    public function getCacheDuration(): int
    {
        return config('laravel-block.cache_duration', 86400);
    }

    /**
     * Check if events are enabled.
     */
    public function eventsEnabled(): bool
    {
        return config('laravel-block.dispatch_events', true);
    }
}
