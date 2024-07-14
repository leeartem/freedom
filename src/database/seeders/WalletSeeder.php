<?php

namespace Database\Seeders;

use App\Entities\User;
use App\Entities\Wallet;
use Database\Factories\WalletFactory;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        Wallet::factory()->create([
            'user_id' => User::first()->id,
            'balance' => 0,
            'amount_blocked' => 0,
        ]);

        Wallet::factory()->create([
            'user_id' => User::skip(1)->first()->id,
            'balance' => 0,
            'amount_blocked' => 0,
        ]);
    }
}
