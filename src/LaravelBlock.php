<?php

namespace TimGavin\LaravelBlock;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use TimGavin\LaravelBlock\Events\UserBlocked;
use TimGavin\LaravelBlock\Events\UserUnblocked;
use TimGavin\LaravelBlock\Models\Block;

trait LaravelBlock
{
    /**
     * Define the blocks relationship (users this user is blocking).
     */
    public function blocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Block::class, 'user_id');
    }

    /**
     * Define the blockers relationship (users blocking this user).
     */
    public function blockers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Block::class, 'blocking_id');
    }

    /**
     * Block the given user.
     *
     * @return bool True if the user was blocked, false if already blocking or invalid.
     */
    public function block(int|Authenticatable $user): bool
    {
        $user_id = is_int($user) ? $user : ($user->id ?? null);

        if ($user_id === null || $user_id === $this->id) {
            return false;
        }

        $block = Block::firstOrCreate([
            'user_id' => $this->id,
            'blocking_id' => $user_id,
        ]);

        if ($block->wasRecentlyCreated) {
            $this->clearBlockingCache();

            if (config('laravel-block.dispatch_events', true)) {
                event(new UserBlocked($this->id, $user_id));
            }

            return true;
        }

        return false;
    }

    /**
     * Unblock the given user.
     *
     * @return bool True if the user was unblocked, false if not blocking or invalid.
     */
    public function unblock(int|Authenticatable $user): bool
    {
        $user_id = is_int($user) ? $user : ($user->id ?? null);

        if ($user_id === null) {
            return false;
        }

        $deleted = Block::where('user_id', $this->id)
            ->where('blocking_id', $user_id)
            ->delete();

        if ($deleted > 0) {
            $this->clearBlockingCache();

            if (config('laravel-block.dispatch_events', true)) {
                event(new UserUnblocked($this->id, $user_id));
            }

            return true;
        }

        return false;
    }

    /**
     * Toggle the block status for a user.
     *
     * @return bool True if now blocking, false if unblocked.
     */
    public function toggleBlock(int|Authenticatable $user): bool
    {
        if ($this->isBlocking($user)) {
            $this->unblock($user);

            return false;
        }

        $this->block($user);

        return true;
    }

    /**
     * Check if a user is blocking the given user.
     */
    public function isBlocking(int|Authenticatable $user): bool
    {
        $user_id = is_int($user) ? $user : ($user->id ?? null);

        if ($user_id === null) {
            return false;
        }

        if (cache()->has('laravel-block:blocking.'.$this->id)) {
            return in_array($user_id, $this->getBlockingCache());
        }

        return Block::toBase()
            ->where('user_id', $this->id)
            ->where('blocking_id', $user_id)
            ->exists();
    }

    /**
     * Check if a user is blocked by the given user.
     */
    public function isBlockedBy(int|Authenticatable $user): bool
    {
        $user_id = is_int($user) ? $user : ($user->id ?? null);

        if ($user_id === null) {
            return false;
        }

        if (cache()->has('laravel-block:blockers.'.$this->id)) {
            return in_array($user_id, $this->getBlockersCache());
        }

        return Block::toBase()
            ->where('user_id', $user_id)
            ->where('blocking_id', $this->id)
            ->exists();
    }

    /**
     * Check if two users mutually block each other.
     */
    public function isMutuallyBlocking(int|Authenticatable $user): bool
    {
        return $this->isBlocking($user) && $this->isBlockedBy($user);
    }

    /**
     * Check if there is any block relationship between this user and another user.
     */
    public function hasBlockWith(int|Authenticatable $user): bool
    {
        $user_id = is_int($user) ? $user : ($user->id ?? null);

        if ($user_id === null) {
            return false;
        }

        return $this->isBlocking($user) || $this->isBlockedBy($user);
    }

    /**
     * Returns the users a user is blocking.
     */
    public function getBlocking(): Collection
    {
        return Block::where('user_id', $this->id)
            ->with('blocking')
            ->get();
    }

    /**
     * Returns the users a user is blocking with pagination.
     */
    public function getBlockingPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Block::where('user_id', $this->id)
            ->with('blocking')
            ->paginate($perPage);
    }

    /**
     * Returns the users who are blocking a user.
     */
    public function getBlockers(): Collection
    {
        return Block::where('blocking_id', $this->id)
            ->with('user')
            ->get();
    }

    /**
     * Returns the users who are blocking a user with pagination.
     */
    public function getBlockersPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Block::where('blocking_id', $this->id)
            ->with('user')
            ->paginate($perPage);
    }

    /**
     * Returns the latest users who are blocking a user.
     */
    public function getLatestBlockers(int $limit = 5): Collection
    {
        return Block::where('blocking_id', $this->id)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Returns the count of users this user is blocking.
     */
    public function getBlockingCount(): int
    {
        return Block::where('user_id', $this->id)->count();
    }

    /**
     * Returns the count of users blocking this user.
     */
    public function getBlockersCount(): int
    {
        return Block::where('blocking_id', $this->id)->count();
    }

    /**
     * Returns IDs of the users a user is blocking.
     */
    public function getBlockingIds(): array
    {
        return Block::toBase()
            ->where('user_id', $this->id)
            ->pluck('blocking_id')
            ->toArray();
    }

    /**
     * Returns IDs of the users who are blocking a user.
     */
    public function getBlockersIds(): array
    {
        return Block::toBase()
            ->where('blocking_id', $this->id)
            ->pluck('user_id')
            ->toArray();
    }

    /**
     * Returns IDs of both users a user is blocking and blockers.
     */
    public function getBlockingAndBlockersIds(): array
    {
        return [
            'blocking' => $this->getBlockingIds(),
            'blockers' => $this->getBlockersIds(),
        ];
    }

    /**
     * Caches IDs of the users a user is blocking.
     */
    public function cacheBlocking(mixed $duration = null): void
    {
        $duration = $duration ?? config('laravel-block.cache_duration', 86400);

        cache()->forget('laravel-block:blocking.'.$this->id);

        cache()->remember('laravel-block:blocking.'.$this->id, $duration, function () {
            return $this->getBlockingIds();
        });
    }

    /**
     * Caches IDs of the users who are blocking a user.
     */
    public function cacheBlockers(mixed $duration = null): void
    {
        $duration = $duration ?? config('laravel-block.cache_duration', 86400);

        cache()->forget('laravel-block:blockers.'.$this->id);

        cache()->remember('laravel-block:blockers.'.$this->id, $duration, function () {
            return $this->getBlockersIds();
        });
    }

    /**
     * Returns the cached IDs of the users a user is blocking.
     */
    public function getBlockingCache(): array
    {
        return cache()->get('laravel-block:blocking.'.$this->id) ?? [];
    }

    /**
     * Returns the cached IDs of the users who are blocking a user.
     */
    public function getBlockersCache(): array
    {
        return cache()->get('laravel-block:blockers.'.$this->id) ?? [];
    }

    /**
     * Clears the Blocking cache.
     */
    public function clearBlockingCache(): void
    {
        cache()->forget('laravel-block:blocking.'.$this->id);
    }

    /**
     * Clears the Blockers cache.
     */
    public function clearBlockersCache(): void
    {
        cache()->forget('laravel-block:blockers.'.$this->id);
    }

    /**
     * Clears the Blockers cache for another user.
     */
    public function clearBlockersCacheFor(int|Authenticatable $user): void
    {
        $user_id = is_int($user) ? $user : ($user->id ?? null);

        if ($user_id !== null) {
            cache()->forget('laravel-block:blockers.'.$user_id);
        }
    }

    /**
     * Clears the Blocking cache for another user.
     */
    public function clearBlockingCacheFor(int|Authenticatable $user): void
    {
        $user_id = is_int($user) ? $user : ($user->id ?? null);

        if ($user_id !== null) {
            cache()->forget('laravel-block:blocking.'.$user_id);
        }
    }

    /**
     * Get block relationships between this user and another user.
     */
    public function getBlockRelationshipsWith(int|Authenticatable $user): Collection
    {
        $user_id = is_int($user) ? $user : ($user->id ?? null);

        if ($user_id === null) {
            return new Collection;
        }

        return Block::where(function ($query) use ($user_id) {
            $query->where('user_id', $this->id)
                ->where('blocking_id', $user_id);
        })->orWhere(function ($query) use ($user_id) {
            $query->where('user_id', $user_id)
                ->where('blocking_id', $this->id);
        })->get();
    }

    /**
     * Get the block record where this user blocks another.
     */
    public function getBlockingRelationship(int|Authenticatable $user): ?Block
    {
        $user_id = is_int($user) ? $user : ($user->id ?? null);

        if ($user_id === null) {
            return null;
        }

        return $this->blocks()
            ->where('blocking_id', $user_id)
            ->first();
    }

    /**
     * Get the block record where another user blocks this user.
     */
    public function getBlockerRelationship(int|Authenticatable $user): ?Block
    {
        $user_id = is_int($user) ? $user : ($user->id ?? null);

        if ($user_id === null) {
            return null;
        }

        return $this->blockers()
            ->where('user_id', $user_id)
            ->first();
    }
}
