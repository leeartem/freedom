<?php

namespace App\Interfaces;

use App\Entities\WalletTransaction;

interface IWalletTransactionRepository
{
    public function create(array $data): WalletTransaction;
    public function update(WalletTransaction $walletTransaction, array $data): WalletTransaction;
    public function findOrFail(int $id): WalletTransaction;
}
