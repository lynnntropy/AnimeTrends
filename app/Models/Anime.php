<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Anime extends Model
{
    protected $table = 'anime';

    public function snapshots()
    {
        return $this
            ->hasMany('App\Models\Snapshot', 'anime_id')
            ->select(['created_at as timestamp', 'rating', 'members']);
    }
}
