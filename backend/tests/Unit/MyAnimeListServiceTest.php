<?php

namespace Tests\Unit;

use App\Services\MyAnimeListService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyAnimeListServiceTest extends TestCase
{
    public function testGetAnime()
    {
        $myAnimeList = new MyAnimeListService();
        $anime = $myAnimeList->getAnime(21);

        $this->assertEquals(21, $anime->id);
        $this->assertEquals('One Piece', $anime->title);
        $this->assertEquals('One Piece', $anime->title_english);
        $this->assertEquals('OP', $anime->synonyms);
        $this->assertNotEmpty($anime->original_image_url);
        $this->assertNotEmpty($anime->rating);
        $this->assertNotEmpty($anime->members);
    }

    public function testGetEpisodesForAnime()
    {
        $myAnimeList = new MyAnimeListService();
        $episodes = $myAnimeList->getEpisodesForAnime(21);

        $this->assertEquals(100, count($episodes));

        $firstEpisode = $episodes[0];
        $this->assertEquals(21, $firstEpisode->anime_id);
        $this->assertEquals("I'm Luffy! The Man Who's Gonna Be King of the Pirates!", $firstEpisode->title);
        $this->assertEquals("Ore wa Luffy! Kaizoku Ou ni Naru Otoko Da!", $firstEpisode->title_romaji);
        $this->assertEquals("俺はルフィ!海賊王になる男だ!", $firstEpisode->title_japanese);
        $this->assertEquals("1999-10-20", $firstEpisode->aired_date->toDateString());
    }

    public function testGetSeasonalAnime()
    {
        $myAnimeList = new MyAnimeListService();
        $seasonalList = $myAnimeList->getSeasonalAnime();

        $this->assertGreaterThan(10, count($seasonalList));

        $firstAnime = $seasonalList[0];
        $this->assertNotEmpty($firstAnime->id);
        $this->assertNotEmpty($firstAnime->title);
        $this->assertNotEmpty($firstAnime->original_image_url);
        $this->assertNotEmpty($firstAnime->rating);
        $this->assertNotEmpty($firstAnime->members);
    }
}
