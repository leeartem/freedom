<?php

namespace App\Providers;

use App\Interfaces\IWalletRepository;
use App\Interfaces\IWalletTransactionRepository;
use App\Repositories\WalletRepository;
use App\Repositories\WalletTransactionRepository;
use App\Services\WalletTransaction\Moderate\ModerateMutex;
use App\Services\WalletTransaction\Deposit\DepositMutex;
use App\Services\WalletTransaction\Transfer\TransferMutex;
use App\Services\WalletTransaction\WriteOff\WriteOffMutex;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // для упрощения я поместил все сюда,
        // но конечно по хорошему нужно зарегистрировать отдельные сервис провайдеры для этих дел
        $this->app->bind(IWalletTransactionRepository::class, WalletTransactionRepository::class);
        $this->app->bind(IWalletRepository::class, WalletRepository::class);

        $this->app->bind(
            DepositMutex::class,
            static function () {
                return new DepositMutex(
                    config('app.mutex_ttl')
                );
            }
        );

        $this->app->bind(
            WriteOffMutex::class,
            static function () {
                return new WriteOffMutex(
                    config('app.mutex_ttl')
                );
            }
        );

        $this->app->bind(
            TransferMutex::class,
            static function () {
                return new TransferMutex(
                    config('app.mutex_ttl')
                );
            }
        );

        $this->app->bind(
            ModerateMutex::class,
            static function () {
                return new ModerateMutex(
                    config('app.mutex_ttl')
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    public function provides(): array
    {
        return [
            IWalletTransactionRepository::class,
            IWalletRepository::class
        ];
    }
}
