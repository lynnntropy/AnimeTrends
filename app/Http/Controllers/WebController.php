<?php

namespace App\Http\Controllers;

use App\Anime;
use Illuminate\Routing\Controller as BaseController;

class WebController extends BaseController
{
    public function home()
    {
        return view('main');
    }

    public function animePage(Anime $anime)
    {
        return view('main', ['anime' => $anime]);
    }
}