<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Anime extends Model
{
    public $incrementing = false;

    protected $guarded = [];
    protected $table = 'anime';
    protected $visible = [
        'id',
        'title',
        'title_english',
        'title_japanese',
        'synonyms',
        'members',
        'rating',
        'archived'
    ];


    public function snapshots()
    {
        return $this
            ->hasMany('App\Models\Snapshot', 'anime_id')
            ->select(['created_at as timestamp', 'rating', 'members']);
    }

    public function episodes()
    {
        return $this
            ->hasMany('App\Models\Episode', 'anime_id')
            ->select(['episode_number', 'title', 'title_romaji', 'title_japanese', 'aired_date']);
    }
}
