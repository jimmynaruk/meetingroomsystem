<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SaveMeetingRoomController extends Controller
{
    //
    
    public function index (Request $request)
    {
        $sql = "SELECT * FROM company_tb ";
        DB::select($sql);
        $dataset = DB::select($sql);

            return view('createMeetingroom',['dataset'=>$dataset]);

    }

    public function saveRoom (Request $request){
    try{
        \Log::info("[".__METHOD__."]"."start");
        $room_size  = $request->input('txtcap');
        $room_price = $request->input('txtprice');
        $company_id = $request->input('txtcpnid');
        $reserve_status = $request->input('txtStatus');
        $create_date = $request->input('txtcreate');

        \Log::info("room_size: ".$room_size.
                    " company_id: ".$company_id.
                    " reserve_status:".$reserve_status.
                    " create_date: ".$create_date );


                $data = array('room_size'  =>$room_size,
                      'company_id'      => $company_id,
                      'reserve_status'  => $reserve_status,
                      'create_date'     =>  $create_date 
                        );

                        
        db::table("room_detail_tb")->insert( $data);
        
        return \Response::json(['message' => 'Create Complete'], 200);
    
    }catch(\Exception $e){

        return $e->getMessage();
        
    }
  
    }

   
  

}
