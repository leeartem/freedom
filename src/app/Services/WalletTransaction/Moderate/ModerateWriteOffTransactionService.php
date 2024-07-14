<?php

namespace App\Services\WalletTransaction\Moderate;

use App\Enums\WalletTransaction\WalletTransactionStatus;

class ModerateWriteOffTransactionService extends AbstractModerateTransactionService
{
    protected function blockTransaction($walletTransaction): void
    {
        if ($walletTransaction->status === WalletTransactionStatus::REJECTED->value) {
            $this->walletRepository->decrementBalance($walletTransaction->user->wallet, $walletTransaction->amount);
        }
        $this->walletRepository->incrementBlockedAmount($walletTransaction->user->wallet, $walletTransaction->amount);
    }

    protected function completeTransaction($walletTransaction): void
    {
        if ($walletTransaction->status === WalletTransactionStatus::REJECTED->value) {
            $this->walletRepository->decrementBalance($walletTransaction->user->wallet, $walletTransaction->amount);
        }
        $this->unblockTransaction($walletTransaction);
    }

    protected function rejectTransaction($walletTransaction): void
    {
        $this->walletRepository->incrementBalance($walletTransaction->user->wallet, $walletTransaction->amount);
        $this->unblockTransaction($walletTransaction);
    }
}
