<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    protected $table = 'anime';

    public function snapshots()
    {
        return $this->hasMany('App\Snapshot', 'anime_id');
    }
}
