<?php

namespace App\Services\WalletTransaction\Moderate;

use App\Dto\WalletTransaction\ModerateTransactionDto;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Exceptions\UnknownTransactionStatusException;
use App\Interfaces\IWalletRepository;
use App\Interfaces\IWalletTransactionRepository;
use App\Services\DistributedMutex\Exceptions\AlreadyLockedException;
use Psr\Log\LoggerInterface;

/**
 * Сервис написан очень примерно
 * все конечно зависит от флоу
 */
abstract class AbstractModerateTransactionService
{
    public function __construct(
        protected IWalletTransactionRepository $walletTransactionRepository,
        protected IWalletRepository            $walletRepository,
        protected ModerateMutex                $moderateMutex,
        protected LoggerInterface              $logger,
    )
    {
    }

    public function run(ModerateTransactionDto $dto): void
    {
        try {
            $this->moderateMutex->lock($dto->walletTransactionId, $dto->status);
        } catch (AlreadyLockedException $exception) {
            $this->logger->error(
                "Логируем ошибку",
                [
                    'wallet_transaction_id' => $dto->walletTransactionId,
                    'status'                => $dto->status,
                ]
            );

            return;
        }

        try {
            $walletTransaction = $this->walletTransactionRepository->findOrFail($dto->walletTransactionId);
            if ($walletTransaction->status === $dto->status) {
                return;
            }

            match ($dto->status) {
                WalletTransactionStatus::BLOCKED->value => $this->blockTransaction($walletTransaction),
                WalletTransactionStatus::COMPLETED->value => $this->completeTransaction($walletTransaction),
                WalletTransactionStatus::REJECTED->value => $this->rejectTransaction($walletTransaction),
                WalletTransactionStatus::FAILED->value,
                WalletTransactionStatus::REFUNDED->value => null,
                default => throw new UnknownTransactionStatusException()
            };

            $this->walletTransactionRepository->update($walletTransaction, [
                'status' => $dto->status,
            ]);

            $this->moderateMutex->release();
        } catch (\Throwable $exception) {
            $this->moderateMutex->release();
            $this->logger->error(
                'Тоже логируем ошибку',
                [
                    'message' => $exception->getMessage(),
                    'trace'   => $exception->getTraceAsString(),
                    'dto'     => $dto,
                ]
            );

            throw $exception;
        }
    }

    protected function blockTransaction($walletTransaction): void
    {
        if ($walletTransaction->status === WalletTransactionStatus::COMPLETED->value) {
            $this->walletRepository->decrementBalance($walletTransaction->user->wallet, $walletTransaction->amount);
        }
        $this->walletRepository->incrementBlockedAmount($walletTransaction->user->wallet, $walletTransaction->amount);
    }

    protected function completeTransaction($walletTransaction): void
    {
        $this->walletRepository->incrementBalance($walletTransaction->user->wallet, $walletTransaction->amount);
        $this->unblockTransaction($walletTransaction);
    }

    protected function rejectTransaction($walletTransaction): void
    {
        if ($walletTransaction->status === WalletTransactionStatus::COMPLETED->value) {
            $this->walletRepository->decrementBalance($walletTransaction->user->wallet, $walletTransaction->amount);
        }
        $this->unblockTransaction($walletTransaction);
    }

    protected function unblockTransaction($walletTransaction): void
    {
        if ($walletTransaction->status === WalletTransactionStatus::BLOCKED->value) {
            $this->walletRepository->decrementBlockedAmount($walletTransaction->user->wallet, $walletTransaction->amount);
        }
    }
}
