<?php

namespace App\Console\Commands;

use App\Models\Savingsgoal;
use Illuminate\Console\Command;

class ResetSaveMoneyToday extends Command
{

    protected $signature = 'app:reset-save-money-today';

    protected $description = 'Command description';


    public function handle()
    {
        Savingsgoal::query()->update(['save_money_today' => 0]);
        $this->info('Đã reset save_money_today về 0 cho tất cả mục tiêu.');
    }
}
