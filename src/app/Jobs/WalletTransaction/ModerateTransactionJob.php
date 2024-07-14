<?php

namespace App\Jobs\WalletTransaction;

use App\Dto\WalletTransaction\ModerateTransactionDto;
use App\Factories\WalletTransaction\ModerateTransactionServiceFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ModerateTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private array $data)
    {
    }

    public function handle(ModerateTransactionServiceFactory $factory): void
    {
        $dto = ModerateTransactionDto::fromArray($this->data);
        $service = $factory->buildByTransactionType($dto->type);
        $service->run($dto);
    }

    public function getWalletTransactionId(): int
    {
        return $this->data['wallet_transaction_id'];
    }

    public function getStatus(): string
    {
        return $this->data['status'];
    }
}
