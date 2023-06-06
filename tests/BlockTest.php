<?php

namespace TimGavin\LaravelBlock\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use TimGavin\LaravelBlock\Models\User;

class BlockTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_block_another_user()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user1->block($user2);

        $this->assertDatabaseHas('blocks', [
            'user_id' => 1,
            'blocking_id' => 2,
        ]);
    }

    /** @test */
    public function a_user_can_block_another_user_by_id()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user1->block($user2->id);

        $this->assertDatabaseHas('blocks', [
            'user_id' => 1,
            'blocking_id' => 2,
        ]);
    }

    /** @test */
    public function a_user_can_unblock_another_user()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user1->block($user2);
        $user1->unblock($user2);

        $this->assertDatabaseMissing('blocks', [
            'user_id' => 1,
            'blocking_id' => 2,
        ]);
    }

    /** @test */
    public function a_user_can_unblock_another_user_by_id()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user1->block($user2->id);
        $user1->unblock($user2->id);

        $this->assertDatabaseMissing('blocks', [
            'user_id' => 1,
            'blocking_id' => 2,
        ]);
    }

    /** @test */
    public function is_a_user_blocking_another_user()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user1->block($user2);

        if ($user1->isBlocking($user2)) {
            $this->assertTrue(true);
        } else {
            $this->fail();
        }
    }

    /** @test */
    public function is_a_user_blocking_another_user_by_id()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user1->block($user2->id);

        if ($user1->isBlocking($user2->id)) {
            $this->assertTrue(true);
        } else {
            $this->fail();
        }
    }

    /** @test */
    public function is_a_user_blocked_by_another_user()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user1->block($user2);

        if ($user2->isBlockedBy($user1)) {
            $this->assertTrue(true);
        } else {
            $this->fail();
        }
    }

    /** @test */
    public function is_a_user_blocked_by_another_user_by_id()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user1->block($user2->id);

        if ($user2->isBlockedBy($user1->id)) {
            $this->assertTrue(true);
        } else {
            $this->fail();
        }
    }

    /** @test */
    public function it_gets_the_users_a_user_is_blocking()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user1->block($user2);

        $blocking = $user1->getBlocking();

        foreach ($blocking as $item) {
            if ($item->blocking->id === 2) {
                $this->assertTrue(true);
            } else {
                $this->fail();
            }
        }
    }

    /** @test */
    public function it_gets_the_ids_of_users_a_user_is_blocking()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user1->block($user2);

        $blockingIds = $user1->getBlockingIds();

        $this->assertContains(2, $blockingIds);
    }

    /** @test */
    public function it_gets_the_users_who_are_blocking_a_user()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user2->block($user1);

        $blockedBy = $user1->getBlockers();

        foreach ($blockedBy as $item) {
            if ($item->blocking->id === 1) {
                $this->assertTrue(true);
            } else {
                $this->fail();
            }
        }
    }

    /** @test */
    public function it_gets_the_ids_of_users_who_are_blocking_a_user()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user2->block($user1);

        $blockedByIds = $user1->getBlockersIds();

        $this->assertContains(2, $blockedByIds);
    }

    /** @test */
    public function it_caches_the_ids_of_users_a_user_is_blocking()
    {
        $user1 = User::create();
        $user2 = User::create();

        $this->actingAs($user1);

        auth()->user()->block($user2);
        auth()->user()->cacheBlocking();

        $this->assertContains(2, cache('blocking.' . auth()->id()));
    }

    /** @test */
    public function it_gets_the_cached_ids_of_users_a_user_is_blocking()
    {
        $user1 = User::create();
        $user2 = User::create();

        $this->actingAs($user1);

        auth()->user()->block($user2);
        auth()->user()->cacheBlocking();

        $this->assertContains(2, auth()->user()->getBlockingCache());
    }

    /** @test */
    public function it_caches_the_ids_of_users_who_are_blocking_a_user()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user2->block($user1);

        $this->actingAs($user1);

        auth()->user()->cacheBlockers();

        $this->assertContains(2, cache('blockers.' . auth()->id()));
    }

    /** @test */
    public function it_gets_the_cached_ids_of_users_who_are_blocking_a_user()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user2->block($user1);

        $this->actingAs($user1);

        auth()->user()->cacheBlockers();

        $this->assertContains(2, auth()->user()->getBlockersCache());
    }

    /** @test */
    public function it_clears_the_cached_ids_of_users_who_are_blocked_by_a_user()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user2->block($user1);

        $this->actingAs($user1);

        auth()->user()->cacheBlocking();

        auth()->user()->clearBlockingCache();

        $cache = auth()->user()->getBlockingCache();

        if (empty($cache)) {
            $this->assertTrue(true);
        } else {
            $this->fail();
        }
    }

    /** @test */
    public function it_clears_the_cached_ids_of_users_who_are_blocking_a_user()
    {
        $user1 = User::create();
        $user2 = User::create();

        $user2->block($user1);

        $this->actingAs($user1);

        auth()->user()->cacheBlockers();

        auth()->user()->clearBlockersCache();

        $cache = auth()->user()->getBlockersCache();

        if (empty($cache)) {
            $this->assertTrue(true);
        } else {
            $this->fail();
        }
    }
}
