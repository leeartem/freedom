<?php

namespace App\Entities;

use Database\Factories\WalletTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'type',
        'status',
        'init_user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function initUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'init_user_id');
    }

    protected static function newFactory(): WalletTransactionFactory
    {
        return WalletTransactionFactory::new();
    }
}
