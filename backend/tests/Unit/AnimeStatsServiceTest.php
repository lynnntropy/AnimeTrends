<?php

namespace Tests\Unit;

use App\Services\AnimeStatsService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnimeStatsServiceTest extends TestCase
{
    public function testGetAnime()
    {
        $animeStats = new AnimeStatsService();
        $anime = $animeStats->getAnime('https://anime-stats.net/anime/show/Free!:%20Eternal%20Summer');

        $this->assertEquals(22265, $anime->malId);
        $this->assertEquals('Free!: Eternal Summer', $anime->name);

        foreach ($anime->eps as $episode) {
            $this->assertNotEmpty($episode->malRating);
            $this->assertNotEmpty($episode->malMembers);
            $this->assertNotEmpty($episode->created_at);
        }
    }

    public function testGetAllAnimeList()
    {
        $animeStats = new AnimeStatsService();
        $allAnime = $animeStats->getAllAnimeList();

        $this->assertGreaterThan(1000, count($allAnime));

        foreach($allAnime as $anime) {
            $this->assertNotEmpty($anime->malId);
            $this->assertNotEmpty($anime->name);
        }
    }

    public function testGetAllAnime()
    {
        $animeStats = new AnimeStatsService();
        $allAnime = $animeStats->getAllAnime();

        $this->assertGreaterThan(1000, count($allAnime));

        foreach($allAnime as $anime) {
            $this->assertNotEmpty($anime->malId);
            $this->assertNotEmpty($anime->name);
        }
    }
}
