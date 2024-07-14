<?php

namespace App\Services\WalletTransaction\WriteOff;

use App\Services\DistributedMutex\BaseMutex;
use App\Services\DistributedMutex\Exceptions\AlreadyLockedException;

/**
 * Также можно было сделать общий мютекс
 * на весь кошелек в целом
 * Но это уже зависит от условий задачи
 */
class WriteOffMutex extends BaseMutex
{
    private string $processPrefix = 'write_off';

    public function __construct(protected int $ttl)
    {
    }

    /**
     * @throws AlreadyLockedException
     */
    public function lock(int $userId, string $amount, string $type): void
    {
        $key = "{$this->processPrefix}_{$userId}_{$amount}_{$type}";
        $this->lockProcess($key);
    }
}
