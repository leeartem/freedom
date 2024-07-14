<?php

namespace App\Services\WalletTransaction\Moderate;

use App\Services\DistributedMutex\BaseMutex;
use App\Services\DistributedMutex\Exceptions\AlreadyLockedException;

/**
 * Также можно было сделать общий мютекс
 * на весь кошелек в целом
 * Но это уже зависит от условий задачи
 */
class ModerateMutex extends BaseMutex
{
    private string $processPrefix = 'authorize';

    public function __construct(protected int $ttl)
    {
    }

    /**
     * @throws AlreadyLockedException
     */
    public function lock(int $walletTransactionId, string $status): void
    {
        $key = "{$this->processPrefix}_{$walletTransactionId}_{$status}";
        $this->lockProcess($key);
    }
}
