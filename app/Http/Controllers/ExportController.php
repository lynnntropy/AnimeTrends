<?php
/**
 * Created by PhpStorm.
 * User: omega
 * Date: 2017-05-16
 * Time: 8:25 PM
 */

namespace App\Http\Controllers;

use App\Anime;
use App\Snapshot;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Ifsnop\Mysqldump as IMysqldump;

class ExportController extends Controller
{
    public function getAnimeCsv()
    {
        Excel::create('anime', function($excel) {
            $excel->sheet('sheet', function($sheet) {
                $sheet->fromModel(Anime::all());
            });
        })->export('csv');
    }

    public function getSnapshotsCsv()
    {
        Excel::create('snapshots', function($excel) {
            $excel->sheet('sheet', function($sheet) {
                $sheet->fromModel(Snapshot::all());
            });
        })->export('csv');
    }

    public function getAnimeJson()
    {
        return Anime::all();
    }

    public function getSnapshotsJson()
    {
        return Snapshot::all();
    }

    public function databaseDump()
    {
        $dump = new IMysqldump\Mysqldump('mysql:host=localhost;dbname=animestocks', env('DB_USERNAME'), env('DB_PASSWORD'));
        $dump->start('animestocks.sql');
        return response()->download('animestocks.sql')->deleteFileAfterSend(true);
    }
}