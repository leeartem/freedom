<?php

namespace App\Console\Commands\WalletTransaction;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Enums\WalletTransaction\WalletTransactionType;
use App\Jobs\WalletTransaction\TransferJob;
use Illuminate\Console\Command;

class FireTransferCommand extends Command
{
    protected $signature = 'fire:transfer';

    protected $description = 'Command to imitate external job firing';

    public function handle(): void
    {
        $dto = new WalletTransactionDto(
            2,
            "50.00",
            WalletTransactionType::TRANSFER->value,
            WalletTransactionStatus::COMPLETED->value,
            1
        );

        TransferJob::dispatch($dto->toArray());
    }
}
