# Upgrading from 1.x to 2.0

This guide covers upgrading from laravel-block 1.x to 2.0.

## Breaking Changes

### Return Type Changes

The `block()` and `unblock()` methods now return `bool` instead of `void`.

**Before (1.x):**
```php
$user->block($otherUser); // returns void
$user->unblock($otherUser); // returns void
```

**After (2.0):**
```php
$result = $user->block($otherUser); // returns true if blocked, false if already blocking
$result = $user->unblock($otherUser); // returns true if unblocked, false if not blocking
```

**Migration:** If you're not using the return values, no changes needed. If you have code that explicitly checks for `null` returns, update it to check for `bool`.

## New Migration

A new migration adds database indexes for improved query performance. Run:

```bash
php artisan migrate
```

Or publish and run the migration:

```bash
php artisan vendor:publish --tag=laravel-block-migrations
php artisan migrate
```

## New Features

### Toggle Method
```php
$result = $user->toggleBlock($otherUser);
// Returns true if now blocking, false if unblocked
```

### Count Methods
```php
$blockingCount = $user->getBlockingCount();
$blockersCount = $user->getBlockersCount();
```

### Mutual Block Check
```php
if ($user->isMutuallyBlocking($otherUser)) {
    // Both users block each other
}
```

### Pagination
```php
$blocking = $user->getBlockingPaginated(15);
$blockers = $user->getBlockersPaginated(15);
```

### Events

Events are now dispatched when users block/unblock each other:

- `TimGavin\LaravelBlock\Events\UserBlocked`
- `TimGavin\LaravelBlock\Events\UserUnblocked`

Listen to these events:
```php
use TimGavin\LaravelBlock\Events\UserBlocked;

Event::listen(UserBlocked::class, function ($event) {
    // $event->userId - the user who blocked
    // $event->blockedId - the user who was blocked
});
```

Disable events via config:
```php
// config/laravel-block.php
'dispatch_events' => false,
```

### Query Scopes

New query scopes on the `Block` model:
```php
use TimGavin\LaravelBlock\Models\Block;

Block::whereUserBlocks($userId)->get();
Block::whereUserIsBlockedBy($userId)->get();
Block::involvingUser($userId)->get();
```

### Configuration

Publish the config file:
```bash
php artisan vendor:publish --tag=laravel-block-config
```

Available options:
```php
return [
    'cache_duration' => 60 * 60 * 24, // 24 hours
    'dispatch_events' => true,
    'user_model' => null, // Falls back to auth config
];
```

### Automatic Cache Invalidation

The blocking/blockers cache is now automatically cleared when you call `block()` or `unblock()`. Manual cache management is still available but often unnecessary.

## Upgrade Steps

1. Update your composer.json:
   ```json
   "timgavin/laravel-block": "^2.0"
   ```

2. Run composer update:
   ```bash
   composer update timgavin/laravel-block
   ```

3. Run migrations:
   ```bash
   php artisan migrate
   ```

4. (Optional) Publish and review config:
   ```bash
   php artisan vendor:publish --tag=laravel-block-config
   ```

5. Update any code that relied on `block()`/`unblock()` returning void.
