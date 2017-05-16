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
            if ($item->type == 'TV' and $item->score != 0 and Anime::find($item->id) == null)
            {
                $newAnime = new Anime;
                $newAnime->id = $item->id;
                $newAnime->title = $item->title;
                $newAnime->image = $item->imageUrl;
                $newAnime->members = $item->members;
                $newAnime->rating = $item->score;
                $newAnime->start = $item->startTimestamp;
                $newAnime->save();
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

        // Loop through (non-archived!) anime,
        // and archive any that are now not on the page.

        $animeList = Anime::where('archived', false)->get();
        foreach($animeList as $item)
        {
            if (!in_array($item->id, $currentIds))
            {
                $item->archived = true;
                $item->save();
            }
        }

        // Generate updated database dumps in a number of formats.

        self::generateCsvDumps();
        self::generateJsonDumps();
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
        $dump = new IMysqldump\Mysqldump('mysql:host=localhost;dbname=animestocks', env('DB_USERNAME'), env('DB_PASSWORD'));
        $dump->start(storage_path('app/public/dumps/animestocks.sql'));
    }
}