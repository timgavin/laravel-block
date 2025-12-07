<?php

namespace TimGavin\LaravelBlock\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserBlocked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $userId,
        public int $blockedId,
    ) {}
}
