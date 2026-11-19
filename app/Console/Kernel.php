<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ALGO-03: penjadwal harian masa berlaku kontrak (UC-03 / UC-18)
        $schedule->command('beasiswa:cek-kontrak')->dailyAt('00:05');
        // ALGO-05b: buka/tutup skema otomatis sesuai tanggal
        $schedule->command('beasiswa:perbarui-skema')->dailyAt('00:10');
        // KAK 7.1.B: backup database otomatis harian dan mingguan
        $schedule->command('beasiswa:backup harian')->dailyAt('01:00');
        $schedule->command('beasiswa:backup mingguan')->weeklyOn(0, '02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
