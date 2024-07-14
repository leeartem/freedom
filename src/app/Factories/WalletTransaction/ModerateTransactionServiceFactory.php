<?php

namespace App\Factories\WalletTransaction;

use App\Enums\WalletTransaction\WalletTransactionType;
use App\Exceptions\UnknownTransactionTypeException;
use App\Services\WalletTransaction\Moderate\ModerateDepositTransactionService;
use App\Services\WalletTransaction\Moderate\AbstractModerateTransactionService;
use App\Services\WalletTransaction\Moderate\ModerateTransferTransactionService;
use App\Services\WalletTransaction\Moderate\ModerateWriteOffTransactionService;
use Illuminate\Contracts\Container\BindingResolutionException;

class ModerateTransactionServiceFactory
{
    /**
     * @throws BindingResolutionException
     * @throws UnknownTransactionTypeException
     */
    public function buildByTransactionType(string $type): AbstractModerateTransactionService
    {
        return match ($type) {
            WalletTransactionType::DEPOSIT->value => app()->make(ModerateDepositTransactionService::class),
            WalletTransactionType::WRITE_OFF->value => app()->make(ModerateWriteOffTransactionService::class),
            WalletTransactionType::TRANSFER->value => app()->make(ModerateTransferTransactionService::class),
            default => throw new UnknownTransactionTypeException()
        };
    }
}
