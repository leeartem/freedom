<?php

namespace App\Dto\WalletTransaction;

readonly class WalletTransactionDto
{
    public function __construct(
        public int    $userId,
        public string $amount,
        public string $type,
        public string $status,
        public ?int   $initUserId = null,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'user_id'      => $this->userId,
            'init_user_id' => $this->initUserId,
            'amount'       => $this->amount,
            'type'         => $this->type,
            'status'       => $this->status,
        ];
    }

    public static function fromArray(array $data): WalletTransactionDto
    {
        return new self(
            userId: $data['user_id'],
            amount: $data['amount'],
            type: $data['type'],
            status: $data['status'],
            initUserId: $data['init_user_id'],
        );
    }
}
