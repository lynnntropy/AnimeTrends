<?php

namespace App\Services;

use Carbon\Carbon;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

class AnimeStatsService
{
    /**
     * @var Client
     */
    protected $goutte;

    /**
     * AnimeStatsService constructor.
     */
    public function __construct()
    {
        $this->goutte = new Client();
        $this->goutte->setClient(new GuzzleClient(['verify' => false])); // SSL cert verification fails if we don't do this
    }

    public function getAnime($url)
    {
        echo $url . "\n";

        $page = $this->goutte->request('GET', $url);

        preg_match('/var anime = (.*);/', $page->html(), $matches);
        $json = $matches[1];

        return json_decode($json);
    }

    public function getAllAnimeList()
    {
        $anime = [];

        $guzzle = new GuzzleClient();
        $response = $guzzle->request('POST', 'https://anime-stats.net/api/v3/lists/all-anime', [
            'json' => [ 'page' => 1, 'search' => '' ]
        ]);

        $totalCount = json_decode($response->getBody()->getContents())->total;
        $pageCount = ceil($totalCount / 50);

        for ($page = 1; $page <= $pageCount; $page++) {

            $response = $guzzle->request('POST', 'https://anime-stats.net/api/v3/lists/all-anime', [
                'json' => [ 'page' => $page, 'search' => '' ]
            ]);

            $responseObject = json_decode($response->getBody()->getContents());
            foreach ($responseObject->anime as $item) $anime[] = $item;
        }

        return $anime;
    }

    public function getAllAnime()
    {
        $animeArray = [];

        $allAnimeList = $this->getAllAnimeList();
        foreach ($allAnimeList as $index => $anime) {
//        foreach (array_slice($allAnimeList, 0, 50) as $index => $anime) {

            if (new Carbon($anime->created_at) >= new Carbon('2017-05-01')) {
                echo "Skipping recent or upcoming show.\n";
                continue;
            }

            echo 'Fetching anime ' . ($index + 1) . '/' . count($allAnimeList) . ': ' . $anime->name . "\n";

            $animeData = $this->getAnime('https://anime-stats.net/anime/show/' . implode('/', array_map('rawurlencode', explode('/', $anime->name))));
            $animeArray[] = $animeData;
        }

        return $animeArray;
    }
}