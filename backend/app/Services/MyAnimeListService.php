<?php

namespace App\Services;

use App\Models\Anime;
use App\Models\Episode;
use App\SeasonalAnimeItem;
use Carbon\Carbon;
use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;


class MyAnimeListService
{
    /**
     * @var Client
     */
    protected $goutte;

    /**
     * MyAnimeListService constructor.
     */
    public function __construct()
    {
        $this->goutte = new Client();
        $this->goutte->setClient(new GuzzleClient(['verify' => false])); // SSL cert verification fails if we don't do this
    }

    /**
     * @param $animeId
     * @return Anime
     */
    public function getAnime($animeId)
    {
        $page = $this->goutte->request('GET', "https://myanimelist.net/anime/$animeId");

        $title = $page->filter('h1.h1 > span')->first()->text();
        $titleEnglish = trim(str_replace('English:', '', $page->filterXPath("//div[@class='spaceit_pad' and ./*[contains(text(), 'English')]]")->first()->text()));
        $titleJapanese = trim(str_replace('Japanese:', '', $page->filterXPath("//div[@class='spaceit_pad' and ./*[contains(text(), 'Japanese')]]")->first()->text()));
        $synonyms = trim(str_replace('Synonyms:', '', $page->filterXPath("//div[@class='spaceit_pad' and ./*[contains(text(), 'Synonyms')]]")->first()->text()));
        $imageUrl = $page->filter('img.ac[itemprop=image]')->first()->attr('src');
        $score =  floatval($page->filter('span[itemprop=ratingValue]')->first()->text());
        $members = trim(str_replace('Members:', '', $page->filterXPath("//div[@class='spaceit' and ./*[contains(text(), 'Members')]]")->first()->text()));

        return new Anime([
            'id' => $animeId,
            'title' => $title,
            'title_english' => $titleEnglish,
            'title_japanese' => $titleJapanese,
            'synonyms' => $synonyms,
            'original_image_url' => $imageUrl,
            'rating' => $score,
            'members' => $members
        ]);
    }

    /**
     * @param $animeId
     * @return Episode[]
     */
    public function getEpisodesForAnime($animeId)
    {
        $page = $this->goutte->request('GET', "https://myanimelist.net/anime/$animeId/_/episode");
        $episodes = [];

        $page->filter('table.ascend tr.episode-list-data')->each(function (Crawler $node) use(&$episodes, $animeId) {

            $airedDateText = trim($node->filter('td.episode-aired')->first()->text());

            if ($airedDateText != 'N/A') {

                $episodeNumber = $node->filter('.episode-number')->first()->text();
                $title = $node->filter('.episode-title > a')->first()->text();

                $secondaryText = $node->filter('.di-ib:not(.fl-r)')->first()->text();
                if ($secondaryText) {
                    $parts = explode('(', $secondaryText);

                    $titleRomaji = trim($parts[0], " \t\n\r\0\x0B\xC2\xA0");

                    if (!empty($parts[1])) {
                        $titleJapanese = trim($parts[1], ')');
                    } else {
                        $titleJapanese = null;
                    }
                } else {
                    $titleRomaji = null;
                    $titleJapanese = null;
                }

                $airedDate = new Carbon($airedDateText);

                $episodes[] = new Episode([
                    'anime_id' => $animeId,
                    'episode_number' => $episodeNumber,
                    'title' => $title,
                    'title_romaji' => $titleRomaji,
                    'title_japanese' => $titleJapanese,
                    'aired_date' => $airedDate
                ]);

            }
        });

        return $episodes;
    }

    /**
     * @return Anime[]
     */
    public function getSeasonalAnime()
    {
        $results = [];

        // Fetch the page into a node
        $page = $this->goutte->request('GET', 'https://myanimelist.net/anime/season');

        // Iterate over each item element
        $page->filter(".seasonal-anime")->each(function (Crawler $node) use(&$results) {

            // Pull the type out of the info element
            preg_match("/ ([[:alpha:]]*) -/", $node->filter("div.info")->first()->text(), $matches);
            $type = $matches[1];

            if ($type == 'TV') {

                // Pull the ID out of the link URL
                preg_match("/myanimelist.net\\/anime\\/(.*)\\//", $node->filter("a.link-title")->first()->attr('href'), $matches);
                $id = intval($matches[1]);

                // Scrape the simple data and parse strings into types as necessary
                $title = $node->filter("a.link-title")->first()->text();
                $score = floatval($node->filter("span.score")->first()->text());
                $members = intval(str_replace(",", "", $node->filter("span.member")->first()->text()));
                $imageElement = $node->filter("div.image > img")->first();
                $imageUrl = $imageElement->attr('data-src') ? $imageElement->attr('data-src') : $imageElement->attr('src');

                $results[] = new Anime([
                    'id' => $id,
                    'title' => $title,
                    'original_image_url' => $imageUrl,
                    'rating' => $score,
                    'members' => $members
                ]);

            }
        });

        return $results;
    }
}