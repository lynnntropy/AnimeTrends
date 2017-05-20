<?php

namespace App\Services;

use App\SeasonalAnimeItem;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;


class SeasonalAnimeService
{
    /**
     * @return array
     */
    public static function getSeasonalAnime()
    {
        $results = [];

        // Create the Goutte client
        $client = new Client();
        $client->setClient(new GuzzleClient(['verify' => false])); // SSL cert verification fails if we don't do this

        // Fetch the page into a node
        $crawler = $client->request('GET', 'https://myanimelist.net/anime/season');

        // Iterate over each item element
        $crawler->filter(".seasonal-anime")->each(function (Crawler $node) use(&$results) {

            // Pull the ID out of the link URL
            preg_match("/myanimelist.net\\/anime\\/(.*)\\//", $node->filter("a.link-title")->first()->attr('href'), $matches);
            $id = intval($matches[1]);

            // Pull the type out of the info element
            preg_match("/ ([[:alpha:]]*) -/", $node->filter("div.info")->first()->text(), $matches);
            $type = $matches[1];

            // Scrape the simple data and parse strings into types as necessary
            $title = $node->filter("a.link-title")->first()->text();
            $score = floatval($node->filter("span.score")->first()->text());
            $members = intval(str_replace(",", "", $node->filter("span.member")->first()->text()));
            $imageElement = $node->filter("div.image > img")->first();
            $imageUrl = $imageElement->attr('data-src') ? $imageElement->attr('data-src') : $imageElement->attr('src');
            $dateString = $node->filter("span.remain-time")->first()->text();

            // Parse the date/time string into a Unix timestamp
            $startTimestamp = strtotime($dateString);

            $results[] = new SeasonalAnimeItem($id, $title, $score, $members, $type, $imageUrl, $startTimestamp);
        });

        return $results;
    }
}