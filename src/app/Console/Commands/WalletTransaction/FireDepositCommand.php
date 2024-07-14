<?php

namespace App\Console\Commands\WalletTransaction;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Enums\WalletTransaction\WalletTransactionType;
use App\Jobs\WalletTransaction\DepositJob;
use Illuminate\Console\Command;

class FireDepositCommand extends Command
{
    protected $signature = 'fire:deposit';

    protected $description = 'Command to imitate external job firing';

    public function handle(): void
    {
        $dto = new WalletTransactionDto(
            1,
            "100.00",
            WalletTransactionType::DEPOSIT->value,
            WalletTransactionStatus::COMPLETED->value,
        );

        DepositJob::dispatch($dto->toArray());
    }
}
