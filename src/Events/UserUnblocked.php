<?php

namespace TimGavin\LaravelBlock\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUnblocked
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $userId,
        public int $unblockedId,
    ) {
    }
}
