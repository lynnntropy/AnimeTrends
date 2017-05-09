<?php
/**
 * Created by PhpStorm.
 * User: omega
 * Date: 2017-05-08
 * Time: 10:30 PM
 */

namespace App\Http\Controllers;

use App\Anime;
use App\Services\SeasonalAnimeService;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    public function getAnimeList()
    {
        return Anime::all();
    }

    public function getSnapshotsForAnime(Anime $anime)
    {
        return $anime->snapshots;
    }
}