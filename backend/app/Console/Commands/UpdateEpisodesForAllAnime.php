<?php

namespace App\Console\Commands;

use App\DatabaseUpdateManager;
use Illuminate\Console\Command;

class UpdateEpisodesForAllAnime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animetrends:updateallepisodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates episodes for all anime in the database.';

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
        $this->info('Updating episodes...');
        $manager = new DatabaseUpdateManager;
        $manager->updateEpisodesForAllAnime();
        $this->info('Update successful.');
    }
}
