<?php

namespace App\Services\WalletTransaction\Deposit;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Interfaces\IWalletRepository;
use App\Interfaces\IWalletTransactionRepository;
use App\Services\DistributedMutex\Exceptions\AlreadyLockedException;
use Psr\Log\LoggerInterface;

class DepositService
{
    public function __construct(
        private IWalletTransactionRepository $walletTransactionRepository,
        private IWalletRepository            $walletRepository,
        private DepositMutex                 $depositMutex,
        private LoggerInterface              $logger,
    )
    {
    }

    public function run(WalletTransactionDto $dto): void
    {
        try {
            $this->depositMutex->lock($dto->userId, $dto->amount, $dto->type);
        } catch (AlreadyLockedException $exception) {
            $this->logger->error(
                "Логируем ошибку",
                [
                    'user_id' => $dto->userId,
                    'amount'  => $dto->amount,
                    'type'    => $dto->type,
                ]
            );

            return;
        }

        try {
            $walletTransaction = $this->walletTransactionRepository->create($dto->toArray());

            if ($walletTransaction->status === WalletTransactionStatus::COMPLETED->value) {
                $this->walletRepository->incrementBalance($walletTransaction->user->wallet, $walletTransaction->amount);
            }

            $this->depositMutex->release();
        } catch (\Throwable $exception) {
            $this->depositMutex->release();
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
}
