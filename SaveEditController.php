<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SaveEditController extends Controller
{
    //
    
    public function index (Request $request)
    {
        if(Session::get('haslogin') == 0 ){
            
            return view('login');
           
        }
        $sql = "SELECT * FROM booking_tb ";
        DB::select($sql);
        $dataset = DB::select($sql);

            return view('editbookroom',['dataset'=>$dataset]);

    }

    public function updateBooking (Request $request)
    {
        try{
            \Log::info("start updateBooking");

                $br_id  = $request->input('txtbrid');
                $room_id = $request->input('txtroomid');
                $startTime = $request->input('txtstart');
                $endTime = $request->input('txtend');
                $update_date = $request->input('txtupdate');

                \Log::info("br_id: ".$br_id.
                    " room_id: ".$room_id.
                    " startTime: ".$startTime.
                    " endTime:".$endTime.
                    " update_date: ".$update_date );
         
    
            $data = array(
                'br_id' => $br_id,
                'room_id' => $room_id, 
                'startTime' => $startTime,
                'endTime' => $endTime,
                'update_date' => $update_date
    
            );

            $result = DB::table('booking_tb')->where('br_id','=',$br_id)->update($data);
            
        return \Response::json(['message' => 'Update Complete'], 200);
        } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
    }
    }
   
    public function Delete (Request $request)
    {   
        $data = $request->json()->all();
        try{

            $validator = \Validator::make($request->all(), [
                'br_id' => 'required'
    
            ]);
          
            $data = array(
                'br_id' => $data['br_id']
               
            );
            //$result = DB::table('room_detail_tb')->truncate($data);
            $result = DB::table('booking_tb')->where('br_id','=',$data)->delete();
            
            if( $data == null || empty($data) || $data == "" ) {
            throw new  \Exception(" Data not found.");
        }
       
        return \Response::json(['message' => 'Delete Complete'], 200);
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

    }
    }
}
