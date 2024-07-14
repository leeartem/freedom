<?php

namespace App\Entities;

use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected static function newFactory(): WalletFactory
    {
        return WalletFactory::new();
    }
}
