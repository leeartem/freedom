<?php

namespace App\Services\WalletTransaction\WriteOff;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Entities\User;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Interfaces\IWalletRepository;
use App\Interfaces\IWalletTransactionRepository;
use App\Services\DistributedMutex\Exceptions\AlreadyLockedException;
use Psr\Log\LoggerInterface;

class WriteOffService
{
    public function __construct(
        private IWalletTransactionRepository $walletTransactionRepository,
        private IWalletRepository            $walletRepository,
        private WriteOffMutex                $writeOffMutex,
        private LoggerInterface              $logger,
    )
    {
    }

    public function run(WalletTransactionDto $dto): void
    {
        try {
            $this->writeOffMutex->lock($dto->userId, $dto->amount, $dto->type);
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
            // TODO для юзеров конечно нужен свой репозиторий, но тут делать его не буду
            $user = User::query()->findOrFail($dto->userId);
            $wallet = $user->wallet;
            if (bccomp($wallet->balance, $dto->amount, 0) < 0) {
                // возможно тут стоило бы создавать транзакцию, но со статусом FAILED
                // зависит от требований
                throw new InsufficientBalanceException();
            }

            $walletTransaction = $this->walletTransactionRepository->create($dto->toArray());

            if ($walletTransaction->status === WalletTransactionStatus::COMPLETED->value) {
                $this->walletRepository->decrementBalance($wallet, $walletTransaction->amount);
            }

            $this->writeOffMutex->release();
        } catch (\Throwable $exception) {
            $this->writeOffMutex->release();
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
