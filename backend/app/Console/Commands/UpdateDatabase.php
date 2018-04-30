<?php

namespace App\Console\Commands;

use App\DatabaseUpdateManager;
use Illuminate\Console\Command;

class UpdateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animetrends:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the anime database and saves updated ratings snapshots for all anime.';

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
        $this->info('Running database update...');
        DatabaseUpdateManager::update();
        $this->info('Database update successful.');
    }
}
