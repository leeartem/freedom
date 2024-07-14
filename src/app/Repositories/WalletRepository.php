<?php

namespace App\Repositories;

use App\Entities\Wallet;
use App\Interfaces\IWalletRepository;

class WalletRepository implements IWalletRepository
{
    public function incrementBalance(Wallet $userWallet, string $amount): void
    {
        $userWallet->increment('balance', $amount);
    }

    public function incrementBlockedAmount(Wallet $userWallet, string $amount): void
    {
        $userWallet->increment('amount_blocked', $amount);
    }

    public function decrementBalance(Wallet $userWallet, string $amount): void
    {
        $userWallet->decrement('balance', $amount);
    }

    public function decrementBlockedAmount(Wallet $userWallet, string $amount): void
    {
        $userWallet->decrement('amount_blocked', $amount);
    }
}
