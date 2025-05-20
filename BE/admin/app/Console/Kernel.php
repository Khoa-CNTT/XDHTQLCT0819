<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{

    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            try {
                app()->make(\App\Http\Controllers\AiController::class)->fetchAndClassifyMBBankTransactionsAuto();
                Log::info('fetchAndClassifyMBBankTransactions chạy xong');
            } catch (\Exception $e) {
                Log::error('Lỗi fetchAndClassifyMBBankTransactions: ' . $e->getMessage());
            }
            try {
                app()->make(\App\Http\Controllers\RecurringtransactionController::class)->processRecurringTransactions();
                Log::info('processRecurringTransactions chạy xong');
            } catch (\Exception $e) {
                Log::error('Lỗi processRecurringTransactions: ' . $e->getMessage());
            }
        })->dailyAt('23:00');
    }


    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
