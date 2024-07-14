<?php

namespace Database\Seeders;

use App\Entities\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
        ]);

        User::factory()->create([
            'name' => 'Another Test User',
            'email' => 'user2@example.com',
        ]);

        (new WalletSeeder())->run();
    }
}
