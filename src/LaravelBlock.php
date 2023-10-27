<?php

namespace TimGavin\LaravelBlock;

use Carbon\Carbon;
use TimGavin\LaravelBlock\Models\Block;

trait LaravelBlock
{
    /**
     * Block the given user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function block(mixed $user): void
    {
        $user_id = is_int($user) ? $user : $user->id;

        Block::firstOrCreate([
            'user_id' => $this->id,
            'blocking_id' => $user_id,
        ]);
    }

    /**
     * Unblock the given user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function unblock(mixed $user): void
    {
        $user_id = is_int($user) ? $user : $user->id;

        Block::where('user_id', $this->id)
            ->where('blocking_id', $user_id)
            ->delete();
    }

    /**
     * Check if a user is blocking the given user.
     *
     * @param  mixed  $user
     * @return bool
     */
    public function isBlocking(mixed $user): bool
    {
        $user_id = is_int($user) ? $user : $user->id;

        if (cache()->has('blocking.' . $this->id)) {
            if (in_array($user_id, $this->getBlockingCache())) {
                return true;
            }

            return false;
        }

        $isBlocking = Block::toBase()
            ->where('user_id', $this->id)
            ->where('blocking_id', $user_id)
            ->first();

        if ($isBlocking) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user is blocked by the given user.
     *
     * @param  mixed  $user
     * @return bool
     */
    public function isBlockedBy(mixed $user): bool
    {
        $user_id = is_int($user) ? $user : $user->id;

        if (cache()->has('blockers.' . $user_id)) {
            if (in_array($user_id, $this->getBlockersCache())) {
                return true;
            }

            return false;
        }

        $isBlockedBy = Block::toBase()
            ->where('user_id', $user_id)
            ->where('blocking_id', $this->id)
            ->first();

        if ($isBlockedBy) {
            return true;
        }

        return false;
    }

    /**
     * Returns the users a user is blocking.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBlocking()
    {
        return Block::where('user_id', $this->id)
            ->with('blocking')
            ->get();
    }

    /**
     * Returns the users who are blocking a user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBlockers()
    {
        return Block::where('blocking_id', $this->id)
            ->with('blockers')
            ->get();
    }

    /**
     * Returns IDs of the users a user is blocking.
     *
     * @return array
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
     *
     * @return array
     */
    public function getBlockersIds(): array
    {
        return Block::toBase()
            ->where('blocking_id', $this->id)
            ->pluck('user_id')
            ->toArray();
    }

    /**
     * Returns IDs of the users a user is blocking.
     * Returns IDs of the users who are blocking a user.
     *
     * @return array
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
     *
     * @param  mixed  $duration
     * @return void
     */
    public function cacheBlocking(mixed $duration = null): void
    {
        $duration ?? Carbon::now()->addDay();

        cache()->forget('blocking.' . $this->id);

        cache()->remember('blocking.' . $this->id, $duration, function () {
            return $this->getBlockingIds();
        });
    }

    /**
     * Caches IDs of the users who are blocking a user.
     *
     * @param  mixed|null  $duration
     * @return void
     */
    public function cacheBlockers(mixed $duration = null): void
    {
        $duration ?? Carbon::now()->addDay();

        cache()->forget('blockers.' . $this->id);

        cache()->remember('blockers.' . $this->id, $duration, function () {
            return $this->getBlockersIds();
        });
    }

    /**
     * Returns IDs of the users a user is blocking.
     *
     * @return array
     *
     * @throws
     */
    public function getBlockingCache(): array
    {
        return cache()->get('blocking.' . $this->id) ?? [];
    }

    /**
     * Returns IDs of the users who are blocking a user.
     *
     * @return array
     *
     * @throws
     */
    public function getBlockersCache(): array
    {
        return cache()->get('blockers.' . $this->id) ?? [];
    }

    /**
     * Clears the Blocking cache.
     *
     * @return void
     */
    public function clearBlockingCache(): void
    {
        cache()->forget('blocking.' . $this->id);
    }

    /**
     * Clears the Blockers cache.
     *
     * @return void
     */
    public function clearBlockersCache(): void
    {
        cache()->forget('blockers.' . $this->id);
    }
}
