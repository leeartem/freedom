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

class ModerateWriteOffServiceTest extends TestCase
{
    private ModerateTransactionServiceFactory $factory;
    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = app()->make(ModerateTransactionServiceFactory::class);
        $this->user = User::factory()->create();
        Wallet::factory()->create([
            'user_id'        => $this->user->id,
            'balance'        => 0,
            'amount_blocked' => 0,
        ]);
    }

    public function testWriteOffBlock()
    {
        $initWallet = $this->user->wallet;

        $walletTransaction = WalletTransaction::factory()->create([
            'user_id' => $this->user->id,
            'amount'  => 1000,
        ]);

        $dto = new ModerateTransactionDto(
            $walletTransaction->id,
            WalletTransactionType::WRITE_OFF->value,
            WalletTransactionStatus::BLOCKED->value
        );
        $service = $this->factory->buildByTransactionType($dto->type);

        $service->run($dto);

        $walletTransaction->refresh();
        $wallet = $initWallet->fresh();

        $this->assertEquals(WalletTransactionStatus::BLOCKED->value, $walletTransaction->status);

        $this->assertTrue(abs($wallet->balance - $initWallet->balance) < 0.0001);
        $this->assertTrue(abs($wallet->amount_blocked - $walletTransaction->amount) < 0.0001);
    }

    public function testWriteOffBlockThenComplete()
    {
        $initWallet = $this->user->wallet;

        $walletTransaction = WalletTransaction::factory()->create([
            'user_id' => $this->user->id,
            'amount'  => 1000,
            'status'  => WalletTransactionStatus::COMPLETED->value
        ]);

        $dto = new ModerateTransactionDto(
            $walletTransaction->id,
            WalletTransactionType::WRITE_OFF->value,
            WalletTransactionStatus::BLOCKED->value
        );
        $service = $this->factory->buildByTransactionType($dto->type);

        $service->run($dto);

        $dto = new ModerateTransactionDto(
            $walletTransaction->id,
            WalletTransactionType::WRITE_OFF->value,
            WalletTransactionStatus::COMPLETED->value
        );

        $service->run($dto);

        $walletTransaction->refresh();
        $wallet = $initWallet->fresh();

        $this->assertEquals(WalletTransactionStatus::COMPLETED->value, $walletTransaction->status);

        $this->assertTrue(abs($wallet->balance - $initWallet->balance) < 0.0001);
        $this->assertTrue(abs($wallet->amount_blocked - $initWallet->amount_blocked) < 0.0001);
    }

    public function testWriteOffBlockThenReject()
    {
        $initWallet = $this->user->wallet;
        $walletTransaction = WalletTransaction::factory()->create([
            'user_id' => $this->user->id,
            'amount'  => 1000,
            'status'  => WalletTransactionStatus::COMPLETED->value
        ]);

        $dto = new ModerateTransactionDto(
            $walletTransaction->id,
            WalletTransactionType::WRITE_OFF->value,
            WalletTransactionStatus::BLOCKED->value
        );
        $service = $this->factory->buildByTransactionType($dto->type);

        $service->run($dto);

        $dto = new ModerateTransactionDto(
            $walletTransaction->id,
            WalletTransactionType::WRITE_OFF->value,
            WalletTransactionStatus::REJECTED->value
        );

        $service->run($dto);

        $walletTransaction->refresh();
        $wallet = $initWallet->fresh();

        $this->assertEquals(WalletTransactionStatus::REJECTED->value, $walletTransaction->status);

        $this->assertTrue(abs($wallet->balance - $initWallet->balance - $walletTransaction->amount) < 0.0001);
        $this->assertTrue(abs($wallet->amount_blocked - $initWallet->amount_blocked) < 0.0001);
    }
}
