<?php

namespace App\Http\Controllers;

use App\Anime;
use App\Snapshot;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    public function getAnimeList()
    {
        return Anime::orderBy('members', 'desc')->get();
    }

    public function getAnime(Anime $anime)
    {
        return $anime;
    }

    public function getSnapshotsForAnime(Anime $anime)
    {
        return $anime->snapshots;
    }

    public function getStats()
    {
        $time = Snapshot::orderBy('id', 'desc')->first()->created_at;
        return [
            "timestamp" => $time->timestamp,
            "diff" => $time->diffForHumans(),
            "anime_count" => Anime::count(),
            "snapshot_count" => Snapshot::count()
        ];
    }
}