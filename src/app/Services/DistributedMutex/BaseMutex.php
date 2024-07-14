<?php

namespace App\Services\DistributedMutex;

use App\Services\DistributedMutex\Exceptions\AlreadyLockedException;
use Illuminate\Support\Facades\Cache;

class BaseMutex
{
    protected string $processKey;

    public function lockProcess(string $processKey): void
    {
        $exists = Cache::has($processKey);
        if ($exists) {
            throw new AlreadyLockedException("Process {$processKey} already locked");
        }
        Cache::set($processKey, 1, $this->ttl);
        $this->processKey = $processKey;
    }

    public function release(): void
    {
        if ($this->processKey) {
            Cache::forget($this->processKey);
        }
    }
}
