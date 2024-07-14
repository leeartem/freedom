<?php

namespace Tests\Unit;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Entities\User;
use App\Entities\Wallet;
use App\Entities\WalletTransaction;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Enums\WalletTransaction\WalletTransactionType;
use App\Services\WalletTransaction\Deposit\DepositMutex;
use App\Services\WalletTransaction\Deposit\DepositService;
use Tests\TestCase;

class DepositServiceTest extends TestCase
{
    private DepositService $service;
    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app()->make(DepositService::class);
        $this->user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function testDepositSuccess()
    {
        $initBalance = $this->user->wallet->balance;
        $dto = new WalletTransactionDto(
            $this->user->id,
            "100.00",
            WalletTransactionType::DEPOSIT->value,
            WalletTransactionStatus::COMPLETED->value,
        );

        $this->service->run($dto);

        $walletTransactions = WalletTransaction::query()->get();

        $this->assertEquals(1, $walletTransactions->count());

        $walletTransaction = $walletTransactions->first();
        $this->assertEquals($dto->userId, $walletTransaction->user_id);
        $this->assertEquals($dto->amount, $walletTransaction->amount);
        $this->assertEquals($dto->type, $walletTransaction->type);
        $this->assertEquals($dto->status, $walletTransaction->status);

        $wallet = $this->user->wallet->refresh();
        $this->assertTrue(abs($wallet->balance - $initBalance - $dto->amount) < 0.0001);
    }

    public function testDepositFailedByMutex()
    {
        $initBalance = $this->user->wallet->balance;
        $dto = new WalletTransactionDto(
            $this->user->id,
            "100.00",
            WalletTransactionType::DEPOSIT->value,
            WalletTransactionStatus::COMPLETED->value,
        );

        $mutex = $this->app->make(DepositMutex::class);
        $mutex->lock($dto->userId, $dto->amount, $dto->type);
        $this->service->run($dto);
        $mutex->release();

        $walletTransactions = WalletTransaction::query()->get();
        $this->assertEquals(0, $walletTransactions->count());

        $wallet = $this->user->wallet->refresh();
        $this->assertTrue(abs($wallet->balance - $initBalance) < 0.0001);
    }
}
