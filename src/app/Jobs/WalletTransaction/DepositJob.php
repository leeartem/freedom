<?php

namespace App\Jobs\WalletTransaction;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Services\WalletTransaction\Deposit\DepositService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DepositJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function handle(DepositService $service): void
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
}
