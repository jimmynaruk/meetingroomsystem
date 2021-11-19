<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EditDetailBookRoomController extends Controller
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
                start_Time,end_Time,Description,update_date,update_by  FROM booking_tb ";

        $dataset['datalist'] = DB::select($sql);

        $sql2 = "SELECT * FROM time_book_tb ";
        $dataset['booking_time'] = DB::select($sql2);
        
        $group_code = $request->input('group_code');
        $sql3 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
		INNER JOIN group_tb gt ON map.group_code = gt.group_code 
		WHERE map.group_code = '1'";
        $dataset['mapfunc'] = DB::select($sql3);
        
        return view('detail_bookroom',['dataset'=>$dataset]);

    }

    public function index2 (Request $request)
    {
        $data = $request->json()->all();
        $sql = "SELECT br_id,room_id,name,tel,email,
                booking_time,booking_date  FROM booking_tb ";

        $dataset['datalist'] = DB::select($sql);
        
        
        return view('detail_bookroom_user',['dataset'=>$dataset]);

    }

    public function updatedetailBooking (Request $request)
    {
        
        try{
           
           $user_id = $request->input('txtuseriddetail');
            $br_id = $request->input('txtbriddetail');
            $room_id = $request->input('txtroomiddetail');
            $update_date = \Carbon\Carbon::now()->timezone('Asia/Bangkok');
            $start_Time = $request->input('txtstartdatetime');
            $end_Time = $request->input('txtenddatetime');
            $Description = $request->input('txtdescriptiondetail');
            $update_by = Session::get('getuser');
            // \Log::info("room_id: ".$room_id.
            // " room_size: ".$room_size.
            // " company_id: ".$company_id.
            // " create_date: ".$create_date.
            // "Description:" .$Description                
           
                
            $data = array('br_id' => $br_id,
            'room_id' => $room_id,
            'start_Time' => $start_Time,
            'end_Time' => $end_Time,
            'Description' => $Description,
            'update_date'=> $update_date,
            'update_by'=>$update_by
            );
            
            $result = DB::table('booking_tb')->where('br_id','=',$br_id)->update($data);
        
            
        return \Response::json(['message' => 'อัพเดทข้อมูลการจองใหม่แล้ว'], 200);
        } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
    }
    }
   
    public function Deletedetailbook (Request $request)
    {   
        
        try{
             $br_id = $request->input('br_id');
         
            $result = DB::table('booking_tb')->where('br_id',$br_id)->delete();
       
        return \Response::json(['message' => 'Delete Complete'], 200);
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

    }
    }

    public function finddetailbook(Request $request)
    {

        try{

            $br_id = $request->input('br_id');

            $sql = "SELECT
            br_id,
            room_id,
            tel,
            email,
            start_Time,
            end_Time,
            Description
        FROM
            booking_tb 
        WHERE br_id = '$br_id'";

        $result = DB::select($sql);

            return $result;
        }catch  (\Exception $e) {

            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
            return \Response::json(['message' => $e->getMessage()], 500);
    
        }
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
        
        return \Response::json(['message' => 'Reject Complete'], 200);
        
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

    }
    
    }
}
