<?php

namespace App\Interfaces;

use App\Entities\Wallet;

interface IWalletRepository
{
    public function incrementBalance(Wallet $userWallet, string $amount): void;
    public function incrementBlockedAmount(Wallet $userWallet, string $amount): void;
    public function decrementBalance(Wallet $userWallet, string $amount): void;
    public function decrementBlockedAmount(Wallet $userWallet, string $amount): void;
}
