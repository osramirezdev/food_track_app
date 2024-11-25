<?php

namespace Store\Console;

use Store\Console\Commands\ConsumeStoreMessages;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    protected $commands = [
        ConsumeStoreMessages::class,
    ];

    protected function schedule(Schedule $schedule): void {}

    protected function commands(): void {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
