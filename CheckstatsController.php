<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class CheckstatsController extends Controller
{
    public function index (Request $request)
    {   
        $chkGroup = Session::get('chk_groupCode')->group_code;

        if ($chkGroup === 2) {
            return redirect('main');
        }
        if(Session::get('haslogin') == 0 ){
            
            return view('login');
           
        }
        $data = $request->json()->all();
        $sql = "SELECT br_id,room_id,name,tel,email,status,
                start_Time,end_Time,Description  FROM booking_tb 
                where status = 'Wait' ";

        $dataset['datalist'] = DB::select($sql);

        $group_code = $request->input('group_code');
        $sql3 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
		INNER JOIN group_tb gt ON map.group_code = gt.group_code 
		WHERE map.group_code = '1'";
        $dataset['mapfunc'] = DB::select($sql3);
        return view('checkstatus',['dataset'=>$dataset]);

    }
    // public function acceptreject(Request $request){
    //     try{

    //         $data = $request->json()->all();
    //     //    \Log::info("[".__METHOD__."]"."start");
          
    //     $br_id = $request->input('br_id');
    //     $status = 'Accept';
            
    //         \Log::info($data);
            
    //         $chack_brid = $this->checkbrid($br_id);
    //         if(empty($chack_brid)){
    //             throw new \Exception("ไม่มีรหัสจอง");
    //            }
    //         if($status == 'Accept'){
               
    //         }elseif($status == 'Reject'){
            
               
                
    //         }else{
    //             throw new \Exception("ไม่มีสถานะนี้");
    //         }
    //          $data = array(
    //          'status' => $status
    //       );
        
    //       $result = DB::table("booking_tb")->where('br_id','=',$br_id)->update($data); 
       
    //         return \Response::json(['message' => 'อัพเดทสถานะการจองแล้ว'], 200);
        
    //     }catch(\Exception $e){
    
    //         \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
    //         return \Response::json(['message' => $e->getMessage()], 500);
            
    //     }
    // }
    public function acceptreject (Request $request)
    {
       
        try{
        $br_id = $request->input('br_id');
        $status = 'Accept';
          
            $data = array('status' => $status
            );
            $result = DB::table('booking_tb')->where('br_id','=',$br_id)->update($data);
        
            
        return \Response::json(['message' => 'อนุมัติการจองแล้ว'], 200);
        } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
    }
    }
    private function checkbrid($br_id){
        $sql = "select * from booking_tb
        where br_id = \"$br_id\"";
        $dataset = DB::select($sql);

        return $dataset;
      
    }
  
    public function reject(Request $request)
    {   
        try{
            \Log::info("start ");
           
            $br_id = $request->input('br_id');
            $status = 'Reject';

            $data = array(
                'status' => $status,
                'RoomPassword'=> ''
             );
            \Log::info("br_id: ".$br_id);
            $result = DB::table('booking_tb')->where('br_id','=',$br_id)->update($data);
        
        return \Response::json(['message' => 'ไม่อนุมัติการจอง'], 200);
        
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

    }
    
    }
}


