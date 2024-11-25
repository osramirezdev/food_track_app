<?php

namespace Kitchen\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Kitchen\Console\Commands\ConsumeFromOrderExchange;
use Kitchen\Console\Commands\ConsumeFromStoreExchange;

class Kernel extends ConsoleKernel {
    protected $commands = [
        ConsumeFromOrderExchange::class,
        ConsumeFromStoreExchange::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        //
    }

    protected function commands(): void {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
