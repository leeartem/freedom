<?php

namespace App\Services\WalletTransaction\Transfer;

use App\Services\DistributedMutex\BaseMutex;
use App\Services\DistributedMutex\Exceptions\AlreadyLockedException;

/**
 * Также можно было сделать общий мютекс
 * на весь кошелек в целом
 * Но это уже зависит от условий задачи
 */
class TransferMutex extends BaseMutex
{
    private string $processPrefix = 'transfer';

    public function __construct(protected int $ttl)
    {
    }

    /**
     * @throws AlreadyLockedException
     */
    public function lock(int $userId, int $initUserId, string $amount, string $type): void
    {
        $key = "{$this->processPrefix}_{$userId}_{$initUserId}_{$amount}_{$type}";
        $this->lockProcess($key);
    }
}
