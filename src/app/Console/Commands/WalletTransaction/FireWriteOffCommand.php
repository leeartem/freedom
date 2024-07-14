<?php

namespace App\Console\Commands\WalletTransaction;

use App\Dto\WalletTransaction\WalletTransactionDto;
use App\Enums\WalletTransaction\WalletTransactionStatus;
use App\Enums\WalletTransaction\WalletTransactionType;
use App\Jobs\WalletTransaction\WriteOffJob;
use Illuminate\Console\Command;

class FireWriteOffCommand extends Command
{
    protected $signature = 'fire:write-off';

    protected $description = 'Command to imitate external job firing';

    public function handle(): void
    {
        $dto = new WalletTransactionDto(
            1,
            "200.00",
            WalletTransactionType::WRITE_OFF->value,
            WalletTransactionStatus::COMPLETED->value,
        );

        WriteOffJob::dispatch($dto->toArray());
    }
}
