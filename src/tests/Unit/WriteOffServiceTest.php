<?php

namespace Tests\Unit;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Entities\User;
use App\Entities\Wallet;
use App\Entities\WalletTransaction;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Enums\WalletTransaction\WalletTransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Services\WalletTransaction\WriteOff\WriteOffMutex;
use App\Services\WalletTransaction\WriteOff\WriteOffService;
use Tests\TestCase;

class WriteOffServiceTest extends TestCase
{
    private WriteOffService $service;
    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app()->make(WriteOffService::class);
        $this->user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 1000,
        ]);
    }

    public function testWriteOffSuccess()
    {
        $initBalance = $this->user->wallet->balance;
        $dto = new WalletTransactionDto(
            $this->user->id,
            "100.00",
            WalletTransactionType::WRITE_OFF->value,
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
        $this->assertTrue(abs($wallet->balance - $initBalance + $dto->amount) < 0.0001);
    }

    public function testWriteOffFailedByMutex()
    {
        $initBalance = $this->user->wallet->balance;
        $dto = new WalletTransactionDto(
            $this->user->id,
            "100.00",
            WalletTransactionType::WRITE_OFF->value,
            WalletTransactionStatus::COMPLETED->value,
        );

        $mutex = $this->app->make(WriteOffMutex::class);
        $mutex->lock($dto->userId, $dto->amount, $dto->type);
        $this->service->run($dto);
        $mutex->release();

        $walletTransactions = WalletTransaction::query()->get();
        $this->assertEquals(0, $walletTransactions->count());

        $wallet = $this->user->wallet->refresh();
        $this->assertTrue(abs($wallet->balance - $initBalance) < 0.0001);
    }

    public function testWriteOffFailedByInsufficientBalance()
    {
        $this->user->wallet->balance = 80;
        $this->user->wallet->save();

        $initBalance = $this->user->wallet->balance;
        $dto = new WalletTransactionDto(
            $this->user->id,
            "100.00",
            WalletTransactionType::WRITE_OFF->value,
            WalletTransactionStatus::COMPLETED->value,
        );

        $this->expectException(InsufficientBalanceException::class);
        $this->service->run($dto);

        $walletTransactions = WalletTransaction::query()->get();
        $this->assertEquals(0, $walletTransactions->count());

        $wallet = $this->user->wallet->refresh();
        $this->assertTrue(abs($wallet->balance - $initBalance) < 0.0001);
    }
}
