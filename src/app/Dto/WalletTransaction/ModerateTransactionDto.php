<?php

namespace App\Dto\WalletTransaction;

readonly class ModerateTransactionDto
{
    public function __construct(
        public int    $walletTransactionId,
        public string $type,
        public string $status,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'wallet_transaction_id' => $this->walletTransactionId,
            'type'                  => $this->type,
            'status'                => $this->status,
        ];
    }

    public static function fromArray(array $data): ModerateTransactionDto
    {
        return new self (
            walletTransactionId: $data['wallet_transaction_id'],
            type: $data['type'],
            status: $data['status'],
        );
    }
}
