<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SaveBookingRoomController extends Controller
{
    //
    
    public function index (Request $request)
    {
        $sql = "SELECT * FROM room_detail_tb ";
        DB::select($sql);
        $dataset = DB::select($sql);

            return view('register1',['dataset'=>$dataset]);

    }

   
    public function saveBookRoom (Request $request){
        try{
           \Log::info("[".__METHOD__."]"."start");
            $room_id = $request->input('txtroomid');
            $name  = $request->input('txtname');
            $tel = $request->input('txttel');
            $email = $request->input('txtmail');
            $startTime = $request->input('txtstart');
            $endTime = $request->input('txtend');
            $reserve_date = $request->input('txtreserve');
            $validator = \Validator::make($request->all(), [
                'Email' => 'required|email|unique:users'
    
            ]);
            if ($validator->fails())
            {
               
            }
            
           
            // Log::info("Fname: ".$Fname.
            //             " Lname: ".$Lname.
            //             " Tel: ".$Tel.
            //             " Email:".$Email);
    
                     $data = array('room_id' => $room_id,
                         'name'  =>$name,
                         'tel'      => $tel,
                         'email'  => $email,
                         'startTime' => $startTime,
                         'endTime' => $endTime,
                         'reserve_date' => $reserve_date
                           );
                            
            db::table("booking_tb")->insert( $data);
            
            return \Response::json(['message' => 'Book Complete'], 200);
        
        }catch(\Exception $e){
    
            return $e->getMessage();
            
        }
      
        }
       

}
