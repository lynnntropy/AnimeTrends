<?php

namespace App\Http\Controllers;

use App\Anime;
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
}