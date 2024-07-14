<?php

namespace Tests\Feature;

use App\Dto\WalletTransaction\ModerateTransactionDto;
use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Enums\WalletTransaction\WalletTransactionType;
use App\Jobs\WalletTransaction\DepositJob;
use App\Jobs\WalletTransaction\ModerateTransactionJob;
use App\Jobs\WalletTransaction\TransferJob;
use App\Jobs\WalletTransaction\WriteOffJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TransactionJobTest extends TestCase
{
    public function testDeposit()
    {
        Queue::fake();

        $dto = new WalletTransactionDto(
            1,
            "100.00",
            WalletTransactionType::DEPOSIT->value,
            WalletTransactionStatus::COMPLETED->value,
        );

        DepositJob::dispatch($dto->toArray());

        Queue::assertPushed(DepositJob::class, function (DepositJob $job) use ($dto) {
            return $job->getUserId() === $dto->userId
                && $job->getAmount() === $dto->amount
                && $job->getType() === $dto->type
                && $job->getStatus() === $dto->status;
        });
    }

    public function testWriteOff()
    {
        Queue::fake();

        $dto = new WalletTransactionDto(
            1,
            "100.00",
            WalletTransactionType::WRITE_OFF->value,
            WalletTransactionStatus::COMPLETED->value,
        );

        WriteOffJob::dispatch($dto->toArray());

        Queue::assertPushed(WriteOffJob::class, function (WriteOffJob $job) use ($dto) {
            return $job->getUserId() === $dto->userId
                && $job->getAmount() === $dto->amount
                && $job->getType() === $dto->type
                && $job->getStatus() === $dto->status;
        });
    }

    public function testTransfer()
    {
        Queue::fake();

        $dto = new WalletTransactionDto(
            2,
            "100.00",
            WalletTransactionType::TRANSFER->value,
            WalletTransactionStatus::COMPLETED->value,
            1
        );

        TransferJob::dispatch($dto->toArray());

        Queue::assertPushed(TransferJob::class, function (TransferJob $job) use ($dto) {
            return $job->getUserId() === $dto->userId
                && $job->getAmount() === $dto->amount
                && $job->getType() === $dto->type
                && $job->getStatus() === $dto->status;
        });
    }

    public function testBlock()
    {
        Queue::fake();

        $dto = new ModerateTransactionDto(
            1,
            WalletTransactionType::DEPOSIT->value,
            WalletTransactionStatus::BLOCKED->value
        );

        ModerateTransactionJob::dispatch($dto->toArray());

        Queue::assertPushed(ModerateTransactionJob::class, function (ModerateTransactionJob $job) use ($dto) {
            return $job->getWalletTransactionId() === $dto->walletTransactionId
                && $job->getStatus() === $dto->status;
        });
    }
}
