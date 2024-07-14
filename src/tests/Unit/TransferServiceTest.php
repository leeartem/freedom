<?php

namespace Tests\Unit;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Entities\User;
use App\Entities\Wallet;
use App\Entities\WalletTransaction;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Enums\WalletTransaction\WalletTransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Services\WalletTransaction\Transfer\TransferMutex;
use App\Services\WalletTransaction\Transfer\TransferService;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    private TransferService $service;
    private User $user;
    private User $initUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = app()->make(TransferService::class);

        $this->initUser = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $this->initUser->id,
            'balance' => 1000,
        ]);

        $this->user = User::factory()->create();
        Wallet::factory()->create([
            'user_id' => $this->user->id,
            'balance' => 1000,
        ]);
    }

    public function testTransferSuccess()
    {
        $initUserBalance = $this->initUser->wallet->balance;
        $initBalance = $this->user->wallet->balance;

        $dto = new WalletTransactionDto(
            $this->user->id,
            "100.00",
            WalletTransactionType::TRANSFER->value,
            WalletTransactionStatus::COMPLETED->value,
            $this->initUser->id
        );

        $this->service->run($dto);

        $walletTransactions = WalletTransaction::query()->get();

        $this->assertEquals(1, $walletTransactions->count());

        $walletTransaction = $walletTransactions->first();
        $this->assertEquals($dto->userId, $walletTransaction->user_id);
        $this->assertEquals($dto->amount, $walletTransaction->amount);
        $this->assertEquals($dto->type, $walletTransaction->type);
        $this->assertEquals($dto->status, $walletTransaction->status);

        $initUserWallet = $this->initUser->wallet->refresh();
        $this->assertTrue(abs($initUserWallet->balance - $initUserBalance + $dto->amount) < 0.0001);

        $userWallet = $this->user->wallet->refresh();
        $this->assertTrue(abs($userWallet->balance - $initBalance - $dto->amount) < 0.0001);


    }

    public function testTransferFailedByMutex()
    {
        $initUserBalance = $this->initUser->wallet->balance;
        $initBalance = $this->user->wallet->balance;

        $dto = new WalletTransactionDto(
            $this->user->id,
            "100.00",
            WalletTransactionType::TRANSFER->value,
            WalletTransactionStatus::COMPLETED->value,
            $this->initUser->id
        );

        $mutex = $this->app->make(TransferMutex::class);
        $mutex->lock($dto->userId, $dto->initUserId, $dto->amount, $dto->type);
        $this->service->run($dto);
        $mutex->release();

        $walletTransactions = WalletTransaction::query()->get();
        $this->assertEquals(0, $walletTransactions->count());

        $initUserWallet = $this->initUser->wallet->refresh();
        $this->assertTrue(abs($initUserWallet->balance - $initUserBalance) < 0.0001);

        $userWallet = $this->user->wallet->refresh();
        $this->assertTrue(abs($userWallet->balance - $initBalance) < 0.0001);
    }

    public function testTransferFailedByInsufficientBalance()
    {
        $this->initUser->wallet->balance = 80;
        $this->initUser->wallet->save();

        $initUserBalance = $this->initUser->wallet->balance;
        $initBalance = $this->user->wallet->balance;

        $dto = new WalletTransactionDto(
            $this->user->id,
            "100.00",
            WalletTransactionType::TRANSFER->value,
            WalletTransactionStatus::COMPLETED->value,
            $this->initUser->id
        );

        $this->expectException(InsufficientBalanceException::class);
        $this->service->run($dto);

        $walletTransactions = WalletTransaction::query()->get();
        $this->assertEquals(0, $walletTransactions->count());

        $initUserWallet = $this->initUser->wallet->refresh();
        $this->assertTrue(abs($initUserWallet->balance - $initUserBalance) < 0.0001);

        $userWallet = $this->user->wallet->refresh();
        $this->assertTrue(abs($userWallet->balance - $initBalance) < 0.0001);
    }
}
