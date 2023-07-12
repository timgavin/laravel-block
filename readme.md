# Laravel Block

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

A simple Laravel package for blocking users.

## Requirements
- Laravel 9 or greater.
- Laravel `User` model.

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

## Usage

Block a user.
```php
auth()->user()->block($user);
```

Unblock a user.
```php
auth()->user()->unblock($user);
```

Check if a user is blocking another user.
```php
@if (auth()->user()->isBlocking($user))
    You are blocking this user.
@endif
```

Check if a user is blocked by another user.
```php
@if (auth()->user()->isBlockedBy($user))
    This user is blocking you.
@endif
```

Returns the users a user is blocking.
```php
auth()->user()->getBlocking();
```

Returns the users who are blocking a user.
```php
auth()->user()->getBlockers();
```

Returns an array of IDs of the users a user is blocking.
```php
auth()->user()->getBlockingIds();
```

Returns an array of IDs of the users who are blocking a user.
```php
auth()->user()->getBlockersIds();
```

Returns an array of IDs of the users a user is blocking, and who is blocking a user
```php
auth()->user()->getBlockingAndBlockersIds()
```

Caches the IDs of the users a user is blocking. Default is 1 day.
```php
// 1 day
auth()->user()->cacheBlocking();

// 1 hour
auth()->user()->cacheBlocking(3600);

// 1 month
auth()->user()->cacheBlocking(Carbon::addMonth());
```

Returns an array of IDs of the users a user is blocking.
```php
auth()->user()->getBlockingCache();
```

Caches the IDs of the users who are blocking a user. Default is 1 day.
```php
auth()->user()->cacheBlockers();
```

Returns an array of IDs of the users who are blocking a user.
```php
auth()->user()->getBlockersCache();
```

Clears the Blocking cache
```php
auth()->user()->clearBlockingCache();
```

Clears the Blockers cache
```php
auth()->user()->clearBlockersCache();
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email tim@timgavin.name instead of using the issue tracker.

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/timgavin/laravel-block.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/timgavin/laravel-block.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/com/timgavin/laravel-block/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/545076824/shield

[link-packagist]: https://packagist.org/packages/timgavin/laravel-block
[link-downloads]: https://packagist.org/packages/timgavin/laravel-block
[link-travis]: https://travis-ci.org/timgavin/laravel-block
[link-styleci]: https://styleci.io/repos/545076824
[link-author]: https://github.com/timgavin
[link-contributors]: ../../contributors
