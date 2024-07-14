<?php

namespace App\Services\WalletTransaction\Moderate;

use App\Enums\WalletTransaction\WalletTransactionStatus;

class ModerateTransferTransactionService extends AbstractModerateTransactionService
{
    protected function blockTransaction($walletTransaction): void
    {
        match ($walletTransaction->status) {
            WalletTransactionStatus::COMPLETED->value => $this->walletRepository->decrementBalance($walletTransaction->user->wallet, $walletTransaction->amount),
            WalletTransactionStatus::REJECTED->value => $this->walletRepository->decrementBalance($walletTransaction->initUser->wallet, $walletTransaction->amount),
        };
        $this->walletRepository->incrementBlockedAmount($walletTransaction->initUser->wallet, $walletTransaction->amount);
        $this->walletRepository->incrementBlockedAmount($walletTransaction->user->wallet, $walletTransaction->amount);
    }

    protected function completeTransaction($walletTransaction): void
    {
        if ($walletTransaction->status === WalletTransactionStatus::REJECTED->value) {
            $this->walletRepository->decrementBalance($walletTransaction->initUser->wallet, $walletTransaction->amount);
        }
        $this->walletRepository->incrementBalance($walletTransaction->user->wallet, $walletTransaction->amount);
        $this->unblockTransaction($walletTransaction);
    }

    protected function rejectTransaction($walletTransaction): void
    {
        if ($walletTransaction->status === WalletTransactionStatus::COMPLETED->value) {
            $this->walletRepository->decrementBalance($walletTransaction->user->wallet, $walletTransaction->amount);
        }
        $this->walletRepository->incrementBalance($walletTransaction->initUser->wallet, $walletTransaction->amount);
        $this->unblockTransaction($walletTransaction);
    }

    protected function unblockTransaction($walletTransaction): void
    {
        if ($walletTransaction->status === WalletTransactionStatus::BLOCKED->value) {
            $this->walletRepository->decrementBlockedAmount($walletTransaction->user->wallet, $walletTransaction->amount);
            $this->walletRepository->decrementBlockedAmount($walletTransaction->initUser->wallet, $walletTransaction->amount);
        }
    }
}
