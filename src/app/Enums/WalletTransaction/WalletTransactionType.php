<?php

namespace App\Enums\WalletTransaction;

enum WalletTransactionType: string
{
    case WRITE_OFF = 'write_off';
    case DEPOSIT = 'deposit';
    case TRANSFER = 'transfer';
}
