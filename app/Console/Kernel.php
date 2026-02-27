<?php

namespace App\Console;

use App\Console\Commands\Academy;
use App\Console\Commands\Academy\FullSyncBadges;
use App\Console\Commands\Academy\ImportHabboAssetsBadges;
use App\Console\Commands\Academy\ImportHabboAssetsFurnis;
use App\Console\Commands\Academy\ImportHabbofurniItems;
use App\Console\Commands\Academy\RepairBadgeMetadata;
use App\Console\Commands\Academy\SyncExternalFurnis;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Academy Commands
        Academy\Optimize::class,
        Academy\Commands::class,
        Academy\Database::class,
        Academy\LocalRunner::class,
        FullSyncBadges::class,
        ImportHabboAssetsBadges::class,
        ImportHabboAssetsFurnis::class,
        ImportHabbofurniItems::class,
        RepairBadgeMetadata::class,
        SyncExternalFurnis::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule
            ->command('badges:import-habboassets --sync-latest-web --web-pages=2')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
