<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class checkBookController extends Controller
{
    public function index (Request $request)
    {
        $data = $request->json()->all();
        $sql = "SELECT booking_date,booking_time,room_id FROM booking_tb";

        $dataset['datalist'] = DB::select($sql);

         return view('detail_roomid',['dataset'=>$dataset]);

    }

    public function checkDateTimeRoomid(Request $request){

       $sql = "SELECT
       *
   FROM
       booking_tb
   WHERE
       room_id = \"5074\" 
   AND booking_date = \"2019-08-30\"
   AND booking_time = \"11.00 - 13.00\" " ;
}
}
