<?php

namespace App\Console\Commands\WalletTransaction;

use App\Dto\WalletTransaction\ModerateTransactionDto;
use App\Factories\WalletTransaction\ModerateTransactionServiceFactory;
use App\Jobs\WalletTransaction\ModerateTransactionJob;
use Illuminate\Console\Command;

class FireModerateTransactionCommand extends Command
{
    protected $signature = 'fire:moderate-transaction {id} {type} {status}';

    protected $description = 'Command to imitate external job firing';

    public function handle(): void
    {
        // я понимаю, что это не дело)
        $dto = new ModerateTransactionDto(
            (int)$this->argument('id'),
            $this->argument('type'),
            $this->argument('status')
        );

        $factory = app(ModerateTransactionServiceFactory::class);
        $service = $factory->buildByTransactionType($dto->type);
        $service->run($dto);

//        ModerateTransactionJob::dispatch($dto->toArray());
    }
}
