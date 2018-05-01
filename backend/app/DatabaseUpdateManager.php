<?php

namespace App;

use App\Models\Anime;
use App\Models\Snapshot;
use App\Services\SeasonalAnimeService;
use Carbon\Carbon;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Jikan\Jikan;

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
                    $newAnime->original_image_url = $item->imageUrl;
                    $newAnime->members = $item->members;
                    $newAnime->rating = $item->score;
                    $newAnime->save();

                    self::fetchImage($item->id, $item->imageUrl);
                }
                else
                {
                    // Check if the image URL has changed in the meantime.

                    if ($existingAnime->original_image_url != $item->imageUrl) {

                        // Fetch the new image.

                        self::fetchImage($item->id, $item->imageUrl);

                        // Update the URL in the database.

                        $existingAnime->original_image_url = $item->imageUrl;
                        $existingAnime->save();
                    }
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

        // Loop through all anime to update any fields that need updating.

        $allAnime = Anime::all();
        foreach($allAnime as $item)
        {
            // Update the show's archived status if it doesn't match
            // the current state of the season page.

            if (!in_array($item->id, $currentIds) && $item->archived == false)
            {
                $item->archived = true;
                $item->archived_at = Carbon::now()->toDateTimeString();
                $item->save();
            }
            else if (in_array($item->id, $currentIds) && $item->archived == true)
            {
                $item->archived = false;
                $item->archived_at = null;
                $item->save();
            }
        }
    }

    public static function fetchAllImages()
    {
        Log::useFiles('php://stdout', 'info');

        // Create the Goutte client
        $client = new Client();
        $client->setClient(new GuzzleClient(['verify' => false])); // SSL cert verification fails if we don't do this

        // Loop over all anime in the database
        $allAnime = Anime::all();
        foreach($allAnime as $item)
        {
            Log::info("Processing anime: " . $item->title . " (ID " . $item->id . ")");
            Log::info("Fetching page...");

            // Fetch the page into a node
            $crawler = $client->request('GET', 'https://myanimelist.net/anime/' . $item->id);

            // Find the image URL
            // (potentially unreliable since MAL's HTML is a crazy mess
            // of tables and no CSS classes to be found anywhere)

            if ($crawler->filter('img[src*="myanimelist.cdn-dena.com/images/anime"]')->count() > 0) {
                $imageUrl = $crawler->filter('img[src*="myanimelist.cdn-dena.com/images/anime"]')->first()->attr('src');
            } else {
                Log::warning("No suitable <img> found on page!");
                continue;
            }

            Log::info("Fetching image...");

            // Fetch the image
            self::fetchImage($item->id, $imageUrl);

            // Update the image URL in the database

            $item->original_image_url = $imageUrl;
            $item->save();

            Log::info("Done.");
        }
    }

    private static function updateRecentlyArchived()
    {
        $jikan = new Jikan;

        $recentlyArchivedAnime = Anime::where('archived', '=', 1)
                            ->whereDate('archived_at', '>=', Carbon::now()->subMonth())
                            ->get();

        foreach ($recentlyArchivedAnime as $anime) {
            $currentData = $jikan->Anime($anime->id)->response;
            $anime->rating = $currentData['score'];
            $anime->members = $currentData['members'];
            $anime->save();
        }
    }

    private static function fetchImage($animeId, $imageUrl)
    {
        $fileContents = file_get_contents($imageUrl);
        Storage::disk('public')->put("cover_images/$animeId.jpg", $fileContents);
    }
}