<?php

namespace Database\Factories;

use App\Entities\WalletTransaction;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Enums\WalletTransaction\WalletTransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->randomNumber(),
            'init_user_id' => null,
            'amount' => rand(0, 100000),
            'type' => WalletTransactionType::DEPOSIT->value,
            'status' => WalletTransactionStatus::COMPLETED->value,
        ];
    }
}
