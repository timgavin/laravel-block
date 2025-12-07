<?php

use TimGavin\LaravelBlock\Models\User;

it('allows a user to block another user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);

    $this->assertDatabaseHas('blocks', [
        'user_id'     => 1,
        'blocking_id' => 2,
    ]);
});

it('allows a user to block another user by id', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2->id);

    $this->assertDatabaseHas('blocks', [
        'user_id'     => 1,
        'blocking_id' => 2,
    ]);
});

it('allows a user to unblock another user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $user1->unblock($user2);

    $this->assertDatabaseMissing('blocks', [
        'user_id'     => 1,
        'blocking_id' => 2,
    ]);
});

it('allows a user to unblock another user by id', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2->id);
    $user1->unblock($user2->id);

    $this->assertDatabaseMissing('blocks', [
        'user_id'     => 1,
        'blocking_id' => 2,
    ]);
});

it('checks if a user is blocking another user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);

    expect($user1->isBlocking($user2))->toBeTrue();
});

it('checks if a user is blocking another user in cache', function () {
    $user1 = User::create();
    $user2 = User::create();

    $this->actingAs($user1);

    $user1->block($user2);
    $user1->cacheBlocking();

    expect($user1->isBlocking($user2))->toBeTrue();
});

it('checks if a user is blocking another user by id', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2->id);

    expect($user1->isBlocking($user2->id))->toBeTrue();
});

it('checks if a user is blocked by another user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);

    expect($user2->isBlockedBy($user1))->toBeTrue();
});

it('checks if a user is blocked by another user in cache', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    $this->actingAs($user1);

    auth()->user()->cacheBlockers();

    expect(auth()->user()->isBlockedBy($user2))->toBeTrue();
});

it('checks if a user is blocked by another user by id', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2->id);

    expect($user2->isBlockedBy($user1->id))->toBeTrue();
});

it('gets the users a user is blocking', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);

    $blocking = $user1->getBlocking();

    expect($blocking)->toHaveCount(1);
    expect($blocking->first()->blocking->id)->toBe(2);
});

it('gets the ids of users a user is blocking', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);

    $blockingIds = $user1->getBlockingIds();

    expect($blockingIds)->toContain(2);
});

it('gets the users who are blocking a user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    $blockers = $user1->getBlockers();

    expect($blockers)->toHaveCount(1);
    expect($blockers->first()->blocking->id)->toBe(1);
});

it('gets the latest users who are blocking a user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    $blockers = $user1->getLatestBlockers(1);

    expect($blockers)->toHaveCount(1);
    expect($blockers->first()->blocking->id)->toBe(1);
});

it('gets the ids of users who are blocking a user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    $blockerIds = $user1->getBlockersIds();

    expect($blockerIds)->toContain(2);
});

it('caches the ids of users a user is blocking', function () {
    $user1 = User::create();
    $user2 = User::create();

    $this->actingAs($user1);

    auth()->user()->block($user2);
    auth()->user()->cacheBlocking();

    expect(cache('laravel-block:blocking.'.auth()->id()))->toContain(2);
});

it('gets the cached ids of users a user is blocking', function () {
    $user1 = User::create();
    $user2 = User::create();

    $this->actingAs($user1);

    auth()->user()->block($user2);
    auth()->user()->cacheBlocking();

    expect(auth()->user()->getBlockingCache())->toContain(2);
});

it('caches the ids of users who are blocking a user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    $this->actingAs($user1);

    auth()->user()->cacheBlockers();

    expect(cache('laravel-block:blockers.'.auth()->id()))->toContain(2);
});

it('gets the cached ids of users who are blocking a user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    $this->actingAs($user1);

    auth()->user()->cacheBlockers();

    expect(auth()->user()->getBlockersCache())->toContain(2);
});

it('clears the cached ids of users a user is blocking', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    $this->actingAs($user1);

    auth()->user()->cacheBlocking();
    auth()->user()->clearBlockingCache();

    expect(auth()->user()->getBlockingCache())->toBeEmpty();
});

it('clears the cached ids of users who are blocking a user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    $this->actingAs($user1);

    auth()->user()->cacheBlockers();
    auth()->user()->clearBlockersCache();

    expect(auth()->user()->getBlockersCache())->toBeEmpty();
});

it('returns the blocks relationship', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);

    expect($user1->blocks)->toHaveCount(1);
    expect($user1->blocks->first()->blocking_id)->toBe(2);
});

it('returns the blockers relationship', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    expect($user1->blockers)->toHaveCount(1);
    expect($user1->blockers->first()->user_id)->toBe(2);
});

it('checks if users have any block relationship', function () {
    $user1 = User::create();
    $user2 = User::create();
    $user3 = User::create();

    $user1->block($user2);

    expect($user1->hasBlockWith($user2))->toBeTrue();
    expect($user2->hasBlockWith($user1))->toBeTrue();
    expect($user1->hasBlockWith($user3))->toBeFalse();
});

it('gets block relationships between two users', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $user2->block($user1);

    $relationships = $user1->getBlockRelationshipsWith($user2);

    expect($relationships)->toHaveCount(2);
});

it('gets the blocking relationship record', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);

    $relationship = $user1->getBlockingRelationship($user2);

    expect($relationship)->not->toBeNull();
    expect($relationship->user_id)->toBe(1);
    expect($relationship->blocking_id)->toBe(2);
});

it('gets the blocker relationship record', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    $relationship = $user1->getBlockerRelationship($user2);

    expect($relationship)->not->toBeNull();
    expect($relationship->user_id)->toBe(2);
    expect($relationship->blocking_id)->toBe(1);
});

it('gets the combined blocking and blockers ids', function () {
    $user1 = User::create();
    $user2 = User::create();
    $user3 = User::create();

    $user1->block($user2);
    $user3->block($user1);

    $ids = $user1->getBlockingAndBlockersIds();

    expect($ids['blocking'])->toContain(2);
    expect($ids['blockers'])->toContain(3);
});

it('prevents a user from blocking themselves', function () {
    $user1 = User::create();

    $user1->block($user1);

    $this->assertDatabaseMissing('blocks', [
        'user_id'     => 1,
        'blocking_id' => 1,
    ]);
});

it('prevents a user from blocking themselves by id', function () {
    $user1 = User::create();

    $user1->block($user1->id);

    $this->assertDatabaseMissing('blocks', [
        'user_id'     => 1,
        'blocking_id' => 1,
    ]);
});

// New v2.0 tests

it('returns true when block is successful', function () {
    $user1 = User::create();
    $user2 = User::create();

    $result = $user1->block($user2);

    expect($result)->toBeTrue();
});

it('returns false when already blocking', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $result = $user1->block($user2);

    expect($result)->toBeFalse();
});

it('returns true when unblock is successful', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $result = $user1->unblock($user2);

    expect($result)->toBeTrue();
});

it('returns false when not blocking', function () {
    $user1 = User::create();
    $user2 = User::create();

    $result = $user1->unblock($user2);

    expect($result)->toBeFalse();
});

it('toggles block on', function () {
    $user1 = User::create();
    $user2 = User::create();

    $result = $user1->toggleBlock($user2);

    expect($result)->toBeTrue();
    expect($user1->isBlocking($user2))->toBeTrue();
});

it('toggles block off', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $result = $user1->toggleBlock($user2);

    expect($result)->toBeFalse();
    expect($user1->isBlocking($user2))->toBeFalse();
});

it('gets the blocking count', function () {
    $user1 = User::create();
    $user2 = User::create();
    $user3 = User::create();

    $user1->block($user2);
    $user1->block($user3);

    expect($user1->getBlockingCount())->toBe(2);
});

it('gets the blockers count', function () {
    $user1 = User::create();
    $user2 = User::create();
    $user3 = User::create();

    $user2->block($user1);
    $user3->block($user1);

    expect($user1->getBlockersCount())->toBe(2);
});

it('checks if users are mutually blocking', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    expect($user1->isMutuallyBlocking($user2))->toBeFalse();

    $user2->block($user1);
    expect($user1->isMutuallyBlocking($user2))->toBeTrue();
});

it('gets blocking with pagination', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);

    $paginated = $user1->getBlockingPaginated(10);

    expect($paginated)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    expect($paginated->total())->toBe(1);
});

it('gets blockers with pagination', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user2->block($user1);

    $paginated = $user1->getBlockersPaginated(10);

    expect($paginated)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    expect($paginated->total())->toBe(1);
});

it('dispatches UserBlocked event when blocking', function () {
    \Illuminate\Support\Facades\Event::fake();

    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);

    \Illuminate\Support\Facades\Event::assertDispatched(
        \TimGavin\LaravelBlock\Events\UserBlocked::class,
        function ($event) {
            return $event->userId === 1 && $event->blockedId === 2;
        }
    );
});

it('dispatches UserUnblocked event when unblocking', function () {
    \Illuminate\Support\Facades\Event::fake();

    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $user1->unblock($user2);

    \Illuminate\Support\Facades\Event::assertDispatched(
        \TimGavin\LaravelBlock\Events\UserUnblocked::class,
        function ($event) {
            return $event->userId === 1 && $event->unblockedId === 2;
        }
    );
});

it('clears cache when blocking', function () {
    $user1 = User::create();
    $user2 = User::create();
    $user3 = User::create();

    $user1->block($user2);
    $user1->cacheBlocking();

    expect($user1->getBlockingCache())->toContain(2);

    $user1->block($user3);

    expect(cache()->has('laravel-block:blocking.1'))->toBeFalse();
});

it('clears cache when unblocking', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $user1->cacheBlocking();

    expect($user1->getBlockingCache())->toContain(2);

    $user1->unblock($user2);

    expect(cache()->has('laravel-block:blocking.1'))->toBeFalse();
});

it('uses query scopes on Block model', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);

    $blocks = \TimGavin\LaravelBlock\Models\Block::whereUserBlocks(1)->get();
    expect($blocks)->toHaveCount(1);

    $blockers = \TimGavin\LaravelBlock\Models\Block::whereUserIsBlockedBy(2)->get();
    expect($blockers)->toHaveCount(1);
});

it('clears the blockers cache for another user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $user2->cacheBlockers();

    expect(cache()->has('laravel-block:blockers.2'))->toBeTrue();

    $user1->clearBlockersCacheFor($user2);

    expect(cache()->has('laravel-block:blockers.2'))->toBeFalse();
});

it('clears the blockers cache for another user by id', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $user2->cacheBlockers();

    expect(cache()->has('laravel-block:blockers.2'))->toBeTrue();

    $user1->clearBlockersCacheFor($user2->id);

    expect(cache()->has('laravel-block:blockers.2'))->toBeFalse();
});

it('clears the blocking cache for another user', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $user1->cacheBlocking();

    expect(cache()->has('laravel-block:blocking.1'))->toBeTrue();

    $user2->clearBlockingCacheFor($user1);

    expect(cache()->has('laravel-block:blocking.1'))->toBeFalse();
});

it('clears the blocking cache for another user by id', function () {
    $user1 = User::create();
    $user2 = User::create();

    $user1->block($user2);
    $user1->cacheBlocking();

    expect(cache()->has('laravel-block:blocking.1'))->toBeTrue();

    $user2->clearBlockingCacheFor($user1->id);

    expect(cache()->has('laravel-block:blocking.1'))->toBeFalse();
});
