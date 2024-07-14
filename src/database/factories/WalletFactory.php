<?php

namespace Database\Factories;

use App\Entities\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->randomNumber(),
            'balance' => rand(0, 100000),
            'amount_blocked' => rand(0, 100000),
        ];
    }
}
