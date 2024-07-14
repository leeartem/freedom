<?php

namespace App\Jobs\WalletTransaction;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Services\WalletTransaction\Transfer\TransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private array $data)
    {
    }

    public function handle(TransferService $service): void
    {
        $dto = WalletTransactionDto::fromArray($this->data);
        $service->run($dto);
    }

    public function getUserId(): int
    {
        return $this->data['user_id'];
    }

    public function getAmount(): string
    {
        return $this->data['amount'];
    }

    public function getType(): string
    {
        return $this->data['type'];
    }

    public function getStatus(): string
    {
        return $this->data['status'];
    }

    public function getInitUserId(): int
    {
        return $this->data['init_user_id'];
    }
}
