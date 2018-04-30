<?php

namespace App\Console\Commands;

use App\DatabaseUpdateManager;
use Illuminate\Console\Command;

class FetchAllImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animetrends:fetchallimages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches fresh images for all anime in the database.';

    /**
     * Create a new command instance.
     *
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
        $this->info('Fetching images...');
        DatabaseUpdateManager::fetchAllImages();
        $this->info('Fetch successful.');
    }
}
