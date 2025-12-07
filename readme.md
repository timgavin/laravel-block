# Laravel Block

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Tests][ico-tests]][link-tests]

A simple Laravel package for blocking users.

## Requirements
- PHP 8.3 or greater
- Laravel 12 or greater

## Installation

Via Composer

``` bash
$ composer require timgavin/laravel-block
```

Import Laravel Block into your User model and add the trait.

```php
namespace App\Models;

use TimGavin\LaravelBlock\LaravelBlock;

class User extends Authenticatable
{
    use LaravelBlock;
}
```

Then run migrations.

```
php artisan migrate
```

## Configuration

Publish the config file.

```bash
php artisan vendor:publish --tag=laravel-block-config
```

Available options:

```php
return [
    'cache_duration' => 60 * 60 * 24, // 24 hours in seconds
    'dispatch_events' => true,
    'user_model' => null, // falls back to auth config
];
```

## Usage

### Block a user

Returns `true` if the user was blocked, `false` if already blocking.

```php
auth()->user()->block($user);
```

### Unblock a user

Returns `true` if the user was unblocked, `false` if not blocking.

```php
auth()->user()->unblock($user);
```

### Toggle block

Returns `true` if now blocking, `false` if unblocked.

```php
auth()->user()->toggleBlock($user);
```

### Check if a user is blocking another user

```php
@if (auth()->user()->isBlocking($user))
    You are blocking this user.
@endif
```

### Check if a user is blocked by another user

```php
@if (auth()->user()->isBlockedBy($user))
    This user is blocking you.
@endif
```

### Check if users are mutually blocking each other

```php
@if (auth()->user()->isMutuallyBlocking($user))
    You are both blocking each other.
@endif
```

### Check if there is any block relationship between two users

```php
@if (auth()->user()->hasBlockWith($user))
    There is a block relationship.
@endif
```

### Get blocking count

```php
auth()->user()->getBlockingCount();
```

### Get blockers count

```php
auth()->user()->getBlockersCount();
```

### Get the users a user is blocking

```php
auth()->user()->getBlocking();
```

### Get the users a user is blocking with pagination

```php
auth()->user()->getBlockingPaginated(15);
```

### Get the users who are blocking a user

```php
auth()->user()->getBlockers();
```

### Get the users who are blocking a user with pagination

```php
auth()->user()->getBlockersPaginated(15);
```

### Get the most recent users who are blocking a user

```php
// default limit is 5
auth()->user()->getLatestBlockers($limit);
```

### Get an array of IDs of the users a user is blocking

```php
auth()->user()->getBlockingIds();
```

### Get an array of IDs of the users who are blocking a user

```php
auth()->user()->getBlockersIds();
```

### Get an array of IDs of both blocking and blockers

```php
auth()->user()->getBlockingAndBlockersIds();
```

## Relationships

Access the blocks relationship (users this user is blocking).

```php
$user->blocks;
```

Access the blockers relationship (users blocking this user).

```php
$user->blockers;
```

Get the block relationship record where this user blocks another.

```php
$user->getBlockingRelationship($otherUser);
```

Get the block relationship record where another user blocks this user.

```php
$user->getBlockerRelationship($otherUser);
```

Get all block relationships between two users.

```php
$user->getBlockRelationshipsWith($otherUser);
```

## Caching

Cache the IDs of the users a user is blocking. Default duration is set in config.

```php
auth()->user()->cacheBlocking();

// custom duration in seconds
auth()->user()->cacheBlocking(3600);
```

Get the cached IDs of the users a user is blocking.

```php
auth()->user()->getBlockingCache();
```

Cache the IDs of the users who are blocking a user.

```php
auth()->user()->cacheBlockers();
```

Get the cached IDs of the users who are blocking a user.

```php
auth()->user()->getBlockersCache();
```

Clear the Blocking cache.

```php
auth()->user()->clearBlockingCache();
```

Clear the Blockers cache.

```php
auth()->user()->clearBlockersCache();
```

Clear the Blockers cache for another user. Useful after blocking a user to keep their blockers cache in sync.

```php
auth()->user()->clearBlockersCacheFor($user);
```

Clear the Blocking cache for another user.

```php
auth()->user()->clearBlockingCacheFor($user);
```

Note: The cache is automatically cleared when calling `block()` or `unblock()`. However, only the current user's cache is cleared. Use `clearBlockersCacheFor()` to clear the target user's blockers cache if needed.

## Events

Events are dispatched when users block or unblock each other.

```php
use TimGavin\LaravelBlock\Events\UserBlocked;
use TimGavin\LaravelBlock\Events\UserUnblocked;

Event::listen(UserBlocked::class, function ($event) {
    // $event->userId - the user who blocked
    // $event->blockedId - the user who was blocked
});

Event::listen(UserUnblocked::class, function ($event) {
    // $event->userId - the user who unblocked
    // $event->unblockedId - the user who was unblocked
});
```

Disable events in config.

```php
'dispatch_events' => false,
```

## Query Scopes

Query scopes are available on the Block model.

```php
use TimGavin\LaravelBlock\Models\Block;

// Get blocks where a user is blocking others
Block::whereUserBlocks($userId)->get();

// Get blocks where a user is being blocked
Block::whereUserIsBlockedBy($userId)->get();

// Get all blocks involving a user
Block::involvingUser($userId)->get();
```

## Upgrading

If upgrading from 1.x, please see the [upgrade guide](UPGRADE.md).

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email tim@timgavin.me instead of using the issue tracker.

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/timgavin/laravel-block.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/timgavin/laravel-block.svg?style=flat-square
[ico-tests]: https://img.shields.io/github/actions/workflow/status/timgavin/laravel-block/tests.yml?branch=master&label=tests&style=flat-square

[link-packagist]: https://packagist.org/packages/timgavin/laravel-block
[link-downloads]: https://packagist.org/packages/timgavin/laravel-block
[link-tests]: https://github.com/timgavin/laravel-block/actions/workflows/tests.yml
[link-author]: https://github.com/timgavin
[link-contributors]: ../../contributors
