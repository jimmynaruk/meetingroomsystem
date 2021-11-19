<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class EventController extends Controller
{
    public function index (Request $request){
        \Log::info("[".__METHOD__."]"."start");
        try{
            
            if(Session::get('haslogin') == 0 ){
            
                return view('login');
               
            }
            
            return view('calendar_book');

        
        }catch(\Exception $e){
    
            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

            return \Response::json(['message' => $e->getMessage()], 500);
            
        }
      
    }
    public function getTime(){
        \Log::info("[".__METHOD__."]"."start");
        
        $sql = "SELECT
        name,
        start_Time,
        end_Time,
        Description,
        room_id,
        status
        
    FROM
        booking_tb  where status = 'Accept' ";
        $dataset['titletime'] = DB::select($sql);
        
        foreach($dataset['titletime'] as $data){
            $eventsColor = "";
            \Log::info($dataset);
            if($data->room_id == ' 1'){
                $eventsColor= 'a';
            }else if($data->room_id == '2'){
                $eventsColor = 'b';
            }else if($data->room_id == '3'){
                $eventsColor = 'c';
            }else if($data->room_id == '4'){

                $eventsColor = 'd';
            }else{
                $eventsColor = 'e';
            
        }
        $result[]=array(  
            "start" =>$data->start_Time,
            "end" =>$data->end_Time,
            "title"=>"เริ่ม".$data->start_Time." ".$data->Description." ห้อง".$data->room_id." สิ้นสุด".$data->end_Time,
            // กำหนด event object property อื่นๆ ที่ต้องการ
            "resourceId"=> $eventsColor
        );    
      

    }
    \Log::info("result".json_encode($result));
    return $result;

}

}