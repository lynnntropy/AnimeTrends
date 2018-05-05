<?php

namespace App;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Snapshot;
use App\Services\AnimeStatsService;
use App\Services\MyAnimeListService;
use Carbon\Carbon;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DatabaseUpdateManager
{
    /**
     * @var MyAnimeListService
     */
    protected $myAnimeList;

    /**
     * @var AnimeStatsService
     */
    protected $animeStats;

    /**
     * DatabaseUpdateManager constructor.
     */
    public function __construct()
    {
        $this->myAnimeList = new MyAnimeListService();
        $this->animeStats = new AnimeStatsService();
    }

    public function update()
    {
        Log::useFiles('php://stdout', 'info');

        $seasonalAnime = $this->myAnimeList->getSeasonalAnime();

        // Update the anime table with anime from the seasonal anime list.

        foreach ($seasonalAnime as $index => $item)
        {
            Log::info("Processing item " . ($index + 1) . "/" . count($seasonalAnime) . ": " . $item->title);

            $existingAnime = Anime::find($item->id);

            if ($existingAnime == null && $item->rating != 0)
            {
                // Add newly added TV anime to the database.

                $item->save();

                $snapshot = new Snapshot;
                $snapshot->rating = $item->rating;
                $snapshot->members = $item->members;
                $item->snapshots()->save($snapshot);

                $this->fetchImage($item->id, $item->original_image_url);
                $this->updateEpisodesForAnime($item->id);
            }
            else if ($existingAnime)
            {
                // Show already exists in the database.

                // Update the score and members, and create a new snapshot

                if ($item->rating != 0) {
                    $existingAnime->members = $item->members;
                    $existingAnime->rating = $item->rating;
                    $existingAnime->save();

                    $snapshot = new Snapshot;
                    $snapshot->rating = $item->rating;
                    $snapshot->members = $item->members;
                    $existingAnime->snapshots()->save($snapshot);
                }

                // Update the episodes
                $this->updateEpisodesForAnime($item->id);

                // Check if the image URL has changed in the meantime.
                if ($existingAnime->original_image_url != $item->original_image_url) {

                    // It's changed, fetch the new image.
                    $this->fetchImage($item->id, $item->original_image_url);

                    // Update the URL in the database.
                    $existingAnime->original_image_url = $item->original_image_url;
                    $existingAnime->save();
                }
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

        $this->updateRecentlyArchived();
    }

    public function fetchAllImages()
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



            while (true) {

                // Fetch the page into a node
                $crawler = $client->request('GET', 'https://myanimelist.net/anime/' . $item->id);

                // Find the image URL
                // (potentially unreliable since MAL's HTML is a crazy mess
                // of tables and no CSS classes to be found anywhere)

                if ($crawler->filter('img[src*="myanimelist.cdn-dena.com/images/anime"]')->count() > 0) {
                    $imageUrl = $crawler->filter('img[src*="myanimelist.cdn-dena.com/images/anime"]')->first()->attr('src');
                    break;
                } else {
                    Log::warning("No suitable <img> found on page! retrying...");
                    usleep(1 * 1000 * 1000);
//                    continue;
                }
            }

            Log::info("Fetching image...");

            // Fetch the image
            $this->fetchImage($item->id, $imageUrl);

            // Update the image URL in the database

            $item->original_image_url = $imageUrl;
            $item->save();

            Log::info("Done.");
        }
    }

    /**
     *  Update recently archived shows.
     *  This method uses Jikan to scrape each show's page individually, rather than using the season page.
     *  It also triggers an update of the episode data for the show.
     */
    private function updateRecentlyArchived()
    {
        $cutoffTime = Carbon::now()->subMonth();

        $recentlyArchivedAnime = Anime::where('archived', '=', 1)
                            ->whereDate('archived_at', '>=', $cutoffTime)
                            ->get();

        foreach ($recentlyArchivedAnime as $anime) {

            // Make sure that the gap between the last snapshot and now
            // is still inside the cutoff time.

            // This prevents from accidentally creating huge gaps in the graph for shows
            // that were recently imported from an external source.

            $lastSnapshotTime = $anime->snapshots()->orderBy('id', 'desc')->first()->created_at;
            if ($lastSnapshotTime >= $cutoffTime) {

                $currentData = $this->myAnimeList->getAnime($anime->id);

                $anime->rating = $currentData->rating;
                $anime->members = $currentData->members;
                $anime->save();

                $snapshot = new Snapshot;
                $snapshot->rating = $currentData->rating;
                $snapshot->members = $currentData->members;
                $anime->snapshots()->save($snapshot);
            }

            $this->updateEpisodesForAnime($anime->id);
        }
    }

    private function fetchImage($animeId, $imageUrl)
    {
        $fileContents = file_get_contents($imageUrl);
        Storage::disk('public')->put("cover_images/$animeId.jpg", $fileContents);
    }

    private function updateEpisodesForAnime($animeId)
    {
        Log::info("Fetching episodes for anime ID $animeId...");

        $episodes = $this->myAnimeList->getEpisodesForAnime($animeId);

        foreach ($episodes as $episode) {
            try {
                Episode::updateOrCreate(
                    ['anime_id' => $animeId, 'episode_number' => $episode->episode_number],
                    [
                        'title' => $episode->title,
                        'title_romaji' => $episode->title_romaji,
                        'title_japanese' => $episode->title_japanese,
                        'aired_date' => $episode->aired_date
                    ]
                );
            } catch (\Exception $e) {
                echo $e;
            }
        }

        Log::info("Fetched and parsed " . count($episodes) . " episodes.");
    }

    public function updateEpisodesForAllAnime()
    {
        Log::useFiles('php://stdout', 'info');

        $allAnime = Anime::all();
        foreach($allAnime as $item) {
            $this->updateEpisodesForAnime($item->id);
        }
    }

    public function scrapeAnimeStats()
    {
        $allAnime = $this->animeStats->getAllAnime();

        foreach($allAnime as $index => $item) {

            echo 'Processing anime ' . ($index + 1) . '/' . count($allAnime) . ': ' . $item->name . "\n";

            if (isset($item->eps)) {

                $lastEpisode = $item->eps[count($item->eps) - 1];

                $existingAnime = Anime::find($item->malId);

                if ($existingAnime == null) {

                    $newAnime = new Anime([
                        'id' => $item->malId,
                        'title' => $item->name,
                        'rating' => $lastEpisode->malRating,
                        'members' => $lastEpisode->malMembers,
                    ]);

                    $newAnime->save();

                    foreach ($item->eps as $episode) {
                        $snapshot = new Snapshot;
                        $snapshot->rating = $episode->malRating;
                        $snapshot->members = $episode->malMembers;
                        $snapshot->created_at = $episode->created_at;
                        $snapshot->updated_at = $episode->created_at;
                        $newAnime->snapshots()->save($snapshot);
                    }

                }

            } else {
                echo "Skipping show with no episodes.\n";
            }
        }
    }

    public function updateTitles()
    {
        $animeToUpdate = Anime::all();
        foreach ($animeToUpdate as $index => $anime) {

            echo 'Fetching anime ' . ($index + 1) . '/' . count($animeToUpdate) . ': ' . $anime->title . "...\n";

                try {
                    $animeData = $this->myAnimeList->getAnime($anime->id);
                } catch (\Exception $e) {
                    echo "Failed: $e \n";
                    continue;
                }

            $anime->title = $animeData->title;
            $anime->title_english = $animeData->title_english;
            $anime->title_japanese = $animeData->title_japanese;
            $anime->synonyms = $animeData->synonyms;
            $anime->save();
        }
    }
}