<?php

namespace App\Console;

use App\Console\Commands\FetchAllImages;
use App\Console\Commands\ScrapeAnimeStats;
use App\Console\Commands\UpdateDatabase;
use App\Console\Commands\UpdateEpisodesForAllAnime;
use App\DatabaseUpdateManager;
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
        UpdateDatabase::class,
        FetchAllImages::class,
        UpdateEpisodesForAllAnime::class,
        ScrapeAnimeStats::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $manager = new DatabaseUpdateManager;
            $manager->update();
        })->twiceDaily();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
