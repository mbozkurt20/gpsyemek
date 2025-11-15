<?php

namespace App\Console;

use App\Enums\MenuItemStatus;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            Restaurant::where('temporary_closed_until', '<', Carbon::now())
                ->update(['temporary_closed_until' => null]);
        })->everyFiveMinutes();

        $schedule->call(function () {
            MenuItem::where('status', MenuItemStatus::INACTIVE)
                ->whereNotNull('closed_until')
                ->where('closed_until', '<=', Carbon::now())
                ->update([
                    'status' => MenuItemStatus::ACTIVE,
                    'closed_until' => null
                ]);
        })->everyMinute();
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
