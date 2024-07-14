<?php

namespace Tests\Unit;

use App\Dto\WalletTransaction\ModerateTransactionDto;
use App\Entities\User;
use App\Entities\Wallet;
use App\Entities\WalletTransaction;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Enums\WalletTransaction\WalletTransactionType;
use App\Factories\WalletTransaction\ModerateTransactionServiceFactory;
use Tests\TestCase;

class ModerateTransferServiceTest extends TestCase
{
    private ModerateTransactionServiceFactory $factory;
    private User $user;
    private User $initUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = app()->make(ModerateTransactionServiceFactory::class);

        $this->initUser = User::factory()->create();
        Wallet::factory()->create([
            'user_id'        => $this->initUser->id,
            'balance'        => 1000,
            'amount_blocked' => 0
        ]);

        $this->user = User::factory()->create();
        Wallet::factory()->create([
            'user_id'        => $this->user->id,
            'balance'        => 1000,
            'amount_blocked' => 0
        ]);
    }

    public function testTransferBlock()
    {
        $initUserInitWallet = $this->initUser->wallet;
        $initWallet = $this->user->wallet;

        $walletTransaction = WalletTransaction::factory()->create([
            'user_id'      => $this->user->id,
            'amount'       => 1000,
            'type'         => WalletTransactionType::TRANSFER->value,
            'init_user_id' => $this->initUser->id,
        ]);

        $dto = new ModerateTransactionDto(
            $walletTransaction->id,
            WalletTransactionType::TRANSFER->value,
            WalletTransactionStatus::BLOCKED->value
        );
        $service = $this->factory->buildByTransactionType($dto->type);

        $service->run($dto);

        $walletTransaction->refresh();
        $initUserWallet = $initUserInitWallet->fresh();
        $wallet = $initWallet->fresh();

        $this->assertEquals(WalletTransactionStatus::BLOCKED->value, $walletTransaction->status);

        $this->assertTrue(abs($initUserWallet->balance - $initUserInitWallet->balance) < 0.0001);
        $this->assertTrue(abs($initUserWallet->amount_blocked - $initUserInitWallet->amount_blocked - $walletTransaction->amount) < 0.0001);

        $this->assertTrue(abs($initWallet->balance - $wallet->balance - $walletTransaction->amount) < 0.0001);
        $this->assertTrue(abs($wallet->amount_blocked - $initWallet->amount_blocked - $walletTransaction->amount) < 0.0001);
    }

    public function testTransferBlockThenComplete()
    {
        $initUserInitWallet = $this->initUser->wallet;
        $initWallet = $this->user->wallet;

        $walletTransaction = WalletTransaction::factory()->create([
            'user_id'      => $this->user->id,
            'amount'       => 1000,
            'status'       => WalletTransactionStatus::COMPLETED->value,
            'init_user_id' => $this->initUser->id,
        ]);

        $dto = new ModerateTransactionDto(
            $walletTransaction->id,
            WalletTransactionType::TRANSFER->value,
            WalletTransactionStatus::BLOCKED->value
        );
        $service = $this->factory->buildByTransactionType($dto->type);

        $service->run($dto);

        $dto = new ModerateTransactionDto(
            $walletTransaction->id,
            WalletTransactionType::TRANSFER->value,
            WalletTransactionStatus::COMPLETED->value
        );

        $service->run($dto);

        $walletTransaction->refresh();
        $initUserWallet = $initUserInitWallet->fresh();
        $wallet = $initWallet->fresh();

        $this->assertEquals(WalletTransactionStatus::COMPLETED->value, $walletTransaction->status);

        $this->assertTrue(abs($initUserWallet->balance - $initUserInitWallet->balance) < 0.0001);
        $this->assertTrue(abs($initUserWallet->amount_blocked - $initUserInitWallet->amount_blocked) < 0.0001);

        $this->assertTrue(abs($initWallet->balance - $wallet->balance) < 0.0001);
        $this->assertTrue(abs($wallet->amount_blocked - $initWallet->amount_blocked) < 0.0001);
    }

    public function testTransferBlockThenReject()
    {
        $initUserInitWallet = $this->initUser->wallet;
        $initWallet = $this->user->wallet;

        $walletTransaction = WalletTransaction::factory()->create([
            'user_id'      => $this->user->id,
            'amount'       => 1000,
            'type'         => WalletTransactionType::TRANSFER->value,
            'status'       => WalletTransactionStatus::COMPLETED->value,
            'init_user_id' => $this->initUser->id,
        ]);

        $dto = new ModerateTransactionDto(
            $walletTransaction->id,
            WalletTransactionType::TRANSFER->value,
            WalletTransactionStatus::BLOCKED->value
        );
        $service = $this->factory->buildByTransactionType($dto->type);

        $service->run($dto);

        $dto = new ModerateTransactionDto(
            $walletTransaction->id,
            WalletTransactionType::TRANSFER->value,
            WalletTransactionStatus::REJECTED->value
        );

        $service->run($dto);

        $walletTransaction->refresh();
        $initUserWallet = $initUserInitWallet->fresh();
        $wallet = $initWallet->fresh();

        $this->assertEquals(WalletTransactionStatus::REJECTED->value, $walletTransaction->status);

        $this->assertTrue(abs($initUserWallet->balance - $initUserInitWallet->balance - $walletTransaction->amount) < 0.0001);
        $this->assertTrue(abs($initUserWallet->amount_blocked - $initUserInitWallet->amount_blocked) < 0.0001);

        $this->assertTrue(abs($initWallet->balance - $wallet->balance - $walletTransaction->amount) < 0.0001);
        $this->assertTrue(abs($wallet->amount_blocked - $initWallet->amount_blocked) < 0.0001);
    }
}
