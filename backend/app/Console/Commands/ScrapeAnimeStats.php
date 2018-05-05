<?php

namespace App\Console\Commands;

use App\DatabaseUpdateManager;
use App\Services\AnimeStatsService;
use Illuminate\Console\Command;

class ScrapeAnimeStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animetrends:scrapeanimestats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape all anime from AnimeStats';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $manager = new DatabaseUpdateManager();
        $manager->scrapeAnimeStats();
    }
}
