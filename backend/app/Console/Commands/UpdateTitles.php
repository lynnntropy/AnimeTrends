<?php

namespace App\Console\Commands;

use App\DatabaseUpdateManager;
use Illuminate\Console\Command;

class UpdateTitles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animetrends:updatetitles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates titles for all anime.';

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
     *
     * @return mixed
     */
    public function handle()
    {
        $manager = new DatabaseUpdateManager();
        $manager->updateTitles();
    }
}
