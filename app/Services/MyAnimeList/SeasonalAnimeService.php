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

        $client = new Client();
        $client->setClient(new GuzzleClient(['verify' => false]));
        $crawler = $client->request('GET', 'https://myanimelist.net/anime/season');

        $crawler->filter(".seasonal-anime")->each(function (Crawler $node) use(&$results) {
            preg_match("/myanimelist.net\\/anime\\/(.*)\\//", $node->filter("a.link-title")->first()->attr('href'), $matches);
            $id = intval($matches[1]);
            $title = $node->filter("a.link-title")->first()->text();
            $score = floatval($node->filter("span.score")->first()->text());
            $members = intval(str_replace(",", "", $node->filter("span.member")->first()->text()));
            preg_match("/ ([[:alpha:]]*) -/", $node->filter("div.info")->first()->text(), $matches);
            $type = $matches[1];
            $imageElement = $node->filter("div.image > img")->first();
            $imageUrl = $imageElement->attr('data-src') ? $imageElement->attr('data-src') : $imageElement->attr('src');
            $results[] = new SeasonalAnimeItem($id, $title, $score, $members, $type, $imageUrl);
        });

        return $results;
    }
}