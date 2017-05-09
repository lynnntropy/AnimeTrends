<?php
/**
 * Created by PhpStorm.
 * User: omega
 * Date: 2017-05-08
 * Time: 10:28 PM
 */

namespace App;


class SeasonalAnimeItem
{
    public $id;
    public $title;
    public $score;
    public $members;
    public $type;
    public $imageUrl;

    /**
     * SeasonalAnimeItem constructor.
     * @param $id
     * @param $title
     * @param $score
     * @param $members
     * @param $type
     * @param $imageUrl
     */
    public function __construct($id, $title, $score, $members, $type, $imageUrl)
    {
        $this->id = $id;
        $this->title = $title;
        $this->score = $score;
        $this->members = $members;
        $this->type = $type;
        $this->imageUrl = $imageUrl;
    }
}