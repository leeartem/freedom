<?php

namespace App\Services\WalletTransaction\Transfer;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Entities\User;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Interfaces\IWalletRepository;
use App\Interfaces\IWalletTransactionRepository;
use App\Services\DistributedMutex\Exceptions\AlreadyLockedException;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;

class TransferService
{
    public function __construct(
        private IWalletTransactionRepository $walletTransactionRepository,
        private IWalletRepository            $walletRepository,
        private TransferMutex                $transferMutex,
        private LoggerInterface              $logger,
    )
    {
    }

    public function run(WalletTransactionDto $dto): void
    {
        try {
            $this->transferMutex->lock($dto->userId, $dto->initUserId, $dto->amount, $dto->type);
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
            $initUser = User::query()->findOrFail($dto->initUserId);
            $initWallet = $initUser->wallet;
            if (bccomp($initWallet->balance, $dto->amount, 0) < 0) {
                throw new InsufficientBalanceException();
            }

            DB::transaction(function () use ($dto, $initWallet) {
                $walletTransaction = $this->walletTransactionRepository->create($dto->toArray());

                if ($walletTransaction->status === WalletTransactionStatus::COMPLETED->value) {
                    $this->walletRepository->decrementBalance($initWallet, $walletTransaction->amount);
                    $this->walletRepository->incrementBalance($walletTransaction->user->wallet, $walletTransaction->amount);
                }
            });

            $this->transferMutex->release();
        } catch (\Throwable $exception) {
            $this->transferMutex->release();
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
