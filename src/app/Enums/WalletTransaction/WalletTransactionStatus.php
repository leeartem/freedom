<?php

namespace App\Enums\WalletTransaction;

enum WalletTransactionStatus: string
{
    case COMPLETED = 'completed';
    case BLOCKED = 'blocked';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case REJECTED = 'rejected';
}
