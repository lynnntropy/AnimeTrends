<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Snapshot;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    public function getAnimeList(Request $request)
    {
        $query = Anime::query();

        if ($request->q) {
            $query->where(function ($query) use ($request) {
                $query->where('title', 'LIKE', '%'.$request->q.'%')
                    ->orWhere('title_english', 'LIKE', '%'.$request->q.'%')
                    ->orWhere('title_japanese', 'LIKE', '%'.$request->q.'%')
                    ->orWhere('synonyms', 'LIKE', '%'.$request->q.'%');
            });
        }

        if ($request->has('archived')) {
            $query->where('archived', '=', $request->archived);
        }

        if ($request->sortBy) {
            $query->orderBy($request->sortBy, $request->input('sortOrder', 'asc'));
        } else {
            $query->orderBy('members', 'desc');
        }

        if ($request->offset) {
            $query->offset($request->offset);
        }

        if ($request->limit) {
            $query->limit($request->limit);
        } else if ($request->offset) {
            # Necessary because OFFSET requires a LIMIT.
            # This is the 'official' solution for MySQL.
            # https://dev.mysql.com/doc/refman/8.0/en/select.html
            $query->limit(PHP_INT_MAX);
        }

        return $query->get();
    }

    public function getAnimeCounts ()
    {
        return [
            'current' => Anime::where('archived', '=', 0)->count(),
            'archived' => Anime::where('archived', '=', 1)->count(),
        ];
    }

    public function getAnime(Anime $anime)
    {
        return [
            'anime' => $anime,
            'snapshotCount' => $anime->snapshots()->count()
        ];
    }

    public function getSnapshotsForAnime(Anime $anime)
    {
        return $anime->snapshots;
    }

    public function getEpisodesForAnime(Anime $anime)
    {
        return $anime->episodes;
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