<?php

namespace App;


use App\Services\SeasonalAnimeService;
use Maatwebsite\Excel\Facades\Excel;
use Ifsnop\Mysqldump as IMysqldump;

class DatabaseUpdateManager
{
    public static function update()
    {
        $seasonalAnime = SeasonalAnimeService::getSeasonalAnime();

        // Update the anime table with anime from the seasonal anime list.

        foreach ($seasonalAnime as $item)
        {
            if ($item->type == 'TV' and $item->score != 0)
            {
                $existingAnime = Anime::find($item->id);

                if ($existingAnime == null)
                {
                    // Add newly added TV anime to the database.
                    $newAnime = new Anime;
                    $newAnime->id = $item->id;
                    $newAnime->title = $item->title;
                    $newAnime->image = $item->imageUrl;
                    $newAnime->members = $item->members;
                    $newAnime->rating = $item->score;
                    $newAnime->start = $item->startTimestamp;
                    $newAnime->save();
                }
                else
                {
                    // Update the image URL in case it's changed in the meantime.
                    $existingAnime->image = $item->imageUrl;
                    $existingAnime->save();
                }
            }
        }

        // Update the snapshots table for all anime that we're currently tracking.

        foreach ($seasonalAnime as $item)
        {
            $anime = Anime::find($item->id);
            if ($anime != null)
            {
                $anime->members = $item->members;
                $anime->rating = $item->score;
                $anime->save();

                $snapshot = new Snapshot;
                $snapshot->rating = $item->score;
                $snapshot->members = $item->members;
                $anime->snapshots()->save($snapshot);
            }
        }

        // Generate a list of IDs currently on the page,
        // so we can check if any anime needs to be archived.

        $currentIds = [];
        foreach ($seasonalAnime as $item) $currentIds[] = $item->id;

        // Loop through all anime,
        // and update their archived status if it doesn't match
        // the current state of the season page.

        $allAnime = Anime::all();
        foreach($allAnime as $item)
        {
            if (!in_array($item->id, $currentIds) && $item->archived == false)
            {
                $item->archived = true;
                $item->save();
            }
            else if (in_array($item->id, $currentIds) && $item->archived == true)
            {
                $item->archived = false;
                $item->save();
            }
        }

        // Generate updated database dumps in a number of formats.

        // CSV and JSON currently disabled because they were causing PHP to run out of memory.

//        self::generateCsvDumps();
//        self::generateJsonDumps();
        self::generateDatabaseDump();
    }

    public static function generateCsvDumps()
    {
        Excel::create('anime', function($excel) {
            $excel->sheet('sheet', function($sheet) {
                $sheet->fromModel(Anime::all());
            });
        })->store('csv', storage_path('app/public/dumps'));

        Excel::create('snapshots', function($excel) {
            $excel->sheet('sheet', function($sheet) {
                $sheet->fromModel(Snapshot::all());
            });
        })->store('csv', storage_path('app/public/dumps'));
    }

    public static function generateJsonDumps()
    {
        $fp = fopen(storage_path('app/public/dumps/anime.json'), 'w');
        fwrite($fp, (string) Anime::all());
        fclose($fp);

        $fp = fopen(storage_path('app/public/dumps/snapshots.json'), 'w');
        fwrite($fp, (string) Snapshot::all());
        fclose($fp);
    }

    public static function generateDatabaseDump()
    {
        $dump = new IMysqldump\Mysqldump('mysql:host=localhost;dbname=animetrends', env('DB_USERNAME'), env('DB_PASSWORD'));
        $dump->start(storage_path('app/public/dumps/animestocks.sql'));
    }
}