<?php

namespace TimGavin\LaravelBlock;

use Carbon\Carbon;
use TimGavin\LaravelBlock\Models\Block;

trait LaravelBlock
{
    /**
     * Block the given user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function block($user)
    {
        Block::firstOrCreate([
            'user_id' => $this->id,
            'blocking_id' => $user->id,
        ]);
    }

    /**
     * Unblock the given user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function unblock($user)
    {
        Block::where('user_id', $this->id)
            ->where('blocking_id', $user->id)
            ->delete();
    }

    /**
     * Check if a user is blocking the given user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function isBlocking($user)
    {
        return Block::toBase()
            ->where('user_id', $this->id)
            ->where('blocking_id', $user->id)
            ->first();
    }

    /**
     * Check if a user is blocked by the given user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function isBlockedBy($user)
    {
        return Block::toBase()
            ->where('user_id', $user->id)
            ->where('blocking_id', $this->id)
            ->first();
    }

    /**
     * Returns the users a user is blocking.
     *
     * @return array
     */
    public function getBlocking()
    {
        return Block::where('user_id', $this->id)
            ->with('blocking')
            ->get();
    }

    /**
     * Returns IDs of the users a user is blocking.
     *
     * @return array
     */
    public function getBlockingIds()
    {
        return Block::toBase()
            ->where('user_id', $this->id)
            ->pluck('blocking_id')
            ->toArray();
    }

    /**
     * Returns the users who are blocking a user.
     *
     * @return array
     */
    public function getBlockers()
    {
        return Block::where('blocking_id', $this->id)
            ->with('blockers')
            ->get();
    }

    /**
     * Returns IDs of the users who are blocking a user.
     *
     * @return array
     */
    public function getBlockersIds()
    {
        return Block::toBase()
            ->where('blocking_id', $this->id)
            ->pluck('user_id')
            ->toArray();
    }

    public function getBlockingAndBlockersIds()
    {
        return [
            'blocking' => $this->getBlockingIds(),
            'blockers' => $this->getBlockersIds(),
        ];
    }

    /**
     * Caches IDs of the users a user is blocking.
     *
     * @param mixed
     * @return array
     */
    public function cacheBlocking($duration = null)
    {
        $duration ?? Carbon::now()->addDay();

        cache()->forget('blocking.' . auth()->id());

        cache()->remember('blocking.' . auth()->id(), $duration, function () {
            return auth()->user()->getBlockingIds();
        });
    }

    /**
     * Returns IDs of the users a user is blocking.
     *
     * @return array
     */
    public function getBlockingCache()
    {
        return cache()->get('blocking.' . auth()->id()) ?? [];
    }

    /**
     * Caches IDs of the users who are blocking a user.
     *
     * @param mixed
     * @return array
     */
    public function cacheBlockers($duration = null)
    {
        $duration ?? Carbon::now()->addDay();

        cache()->forget('blockers.' . auth()->id());

        cache()->remember('blockers.' . auth()->id(), $duration, function () {
            return auth()->user()->getBlockersIds();
        });
    }

    /**
     * Returns IDs of the users who are blocking a user.
     *
     * @return array
     */
    public function getBlockersCache()
    {
        return cache()->get('blockers.' . auth()->id()) ?? [];
    }
}
