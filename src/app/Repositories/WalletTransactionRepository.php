<?php

namespace App\Repositories;

use App\Entities\WalletTransaction;
use App\Interfaces\IWalletTransactionRepository;

class WalletTransactionRepository implements IWalletTransactionRepository
{
    public function create(array $data): WalletTransaction
    {
        return WalletTransaction::query()->create($data);
    }

    public function update(WalletTransaction $walletTransaction, array $data): WalletTransaction
    {
        return tap($walletTransaction)->update($data);
    }

    public function findOrFail(int $id): WalletTransaction
    {
        return WalletTransaction::query()->findOrFail($id);
    }
}
