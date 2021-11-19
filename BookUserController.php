<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Endroid\QrCode\QrCode;
class BookUserController extends Controller
{
    public function index(Request $request)
    {   
        if(Session::get('haslogin') == 0 ){
            
            return view('login');
           
        }
        $user = Session::get('getuser');
       
        $sql = "SELECT * FROM booking_tb
        where name= \"$user\" and (status = 'Accept' or status = 'Reject')";
        $dataset['userdetailbook'] = DB::select($sql);

        $sql2 = "SELECT * FROM time_book_tb ";
        $dataset['booking_time'] = DB::select($sql2);

        $group_code = $request->input('group_code');
        $sql3 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
		INNER JOIN group_tb gt ON map.group_code = gt.group_code 
		WHERE map.group_code = '2'";
        $dataset['mapfunc'] = DB::select($sql3);

        $sql4 = "SELECT * FROM room_detail_tb
        ";
      
        $dataset['roomid'] = DB::select($sql4);

        
        
        return view('book_user',['dataset'=>$dataset]);
    }
    
    public function index2(Request $request)
    {   
        if(Session::get('haslogin') == 0 ){
            
            return view('login');
           
        }
        $sql = "SELECT * FROM booking_tb";

        $dataset['userdetailbook'] = DB::select($sql);

        // \Log::info($dataset);
        return view('detail_bookuser',['dataset'=>$dataset]);
    }

    //qrcode 
    public function genarateqr(Request $request){

        
        $password = $request->input('RoomPassword');

        
        $qrCode = new QrCode($password);
        
        header('Content-Type: '.$qrCode->getContentType());
         $qrCode->writeString();
      \Log::info($qrCode->writeDataUri());
        return $qrCode->writeDataUri();

    }


    public function finddetailUserBook(Request $request)
    {

        try{


            $sql = "SELECT
            bktb.br_id,
            bktb.user_id,
            bktb.room_id,
            bktb.name,
            bktb.tel,
            bktb.email,
            bktb.booking_date,
            tb.booking_time,
            tb.id_booktime,
            bktb.Description
        FROM
            booking_tb bktb
        INNER JOIN time_book_tb tb ON tb.booking_time = bktb.booking_time
        WHERE br_id = '$br_id'";

        $result = DB::select($sql);

            return $result;
        }catch  (\Exception $e) {

            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
            return \Response::json(['message' => $e->getMessage()], 500);
    
        }
    }
    

    public function updatedetailUserBooking (Request $request)
    {
       
        try{
           
            $user_id = $request->input('txtuseriddetail');
            $br_id = $request->input('txtbriddetail');
            $room_id = $request->input('txtroomiddetail');
            $name = $request->input('txtnamedetail');
            $tel = $request->input('txtteldetail');
            $email = $request->input('txtemaildetail');
            $booking_date = $request->input('txtbookdetail');
            $booking_time = $request->input('txtbooktimedetail');
            $Description = $request->input('txtdescriptiondetail');

            // \Log::info("room_id: ".$room_id.
            // " room_size: ".$room_size.
            // " company_id: ".$company_id.
            // " create_date: ".$create_date.
            // "Description:" .$Description                
    
                
            $data = array('br_id' => $br_id,
            'user_id' => $user_id,
            'room_id' => $room_id,
            'name' => $name,
            'tel' => $tel,
            'email' => $email,
            'booking_date' => $booking_date,
            'booking_time' => $booking_time,
            'Description' => $Description
            );
            $result = DB::table('booking_tb')->where('br_id','=',$br_id)->update($data);
        
            
        return \Response::json(['message' => 'อัพเดทข้อมูลการจองใหม่แล้ว'], 200);
        } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
    }
    }

    public function findroomid(Request $request)
    {

        try{

            $sql = "SELECT
            bktb.br_id,
            bktb.room_id,
            bktb.name,
            bktb.tel,
            bktb.email,
            bktb.Description
        FROM
            booking_tb bktb
        WHERE room_id = '$room_id'";

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
                'RoomPassword'=>''
             );
            \Log::info("br_id: ".$br_id);
            $result = DB::table('booking_tb')->where('br_id',$br_id)->update($data);
        
        return \Response::json(['message' => 'Reject Complete'], 200);
        
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

    }
    
    }
}
