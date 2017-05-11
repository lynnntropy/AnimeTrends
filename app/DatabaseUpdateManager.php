<?php
/**
 * Created by PhpStorm.
 * User: omega
 * Date: 2017-05-09
 * Time: 6:14 PM
 */

namespace App;


use App\Services\SeasonalAnimeService;

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
                $anime->save();

                $snapshot = new Snapshot;
                $snapshot->rating = $item->score;
                $snapshot->members = $item->members;
                $anime->snapshots()->save($snapshot);
            }
        }
    }
}