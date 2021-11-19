<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class editbookwaitController extends Controller
{
    public function index(Request $request)
    {   
        if(Session::get('haslogin') == 0 ){
            
            return view('login');
           
        }
        $user = Session::get('getuser');
       
        $sql = "SELECT * FROM booking_tb
        where name= \"$user\" and status = 'Wait' ";
        \Log::info($sql);
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

        
        
        return view('editbookwait',['dataset'=>$dataset]);
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



    public function finddetailbookwait(Request $request)
    {

        try{

            $br_id = $request->input('br_id');
            $sql = "SELECT
            br_id,
            name,
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
    

    
    public function updatedetaileditbookwait (Request $request)
    {
       
        try{
            $data = $request->json()->all();
            $br_id = $request->input('txtbriddetail');
            $room_id = $request->input('txtroomiddetail');
            $name = $request->input('txtnamedetail');
            $tel = $request->input('txtteldetail');
            $status = 'Wait';
            $Description = $request->input('txtdescriptiondetail');
            $start_Time = new \DateTime($request['txtstartdatetime']);
            $end_Time = new \DateTime($request['txtenddatetime']);

            
            $checktime1 = $this->checktime($start_Time,$end_Time);
            $start_Time = $request->input('txtstartdatetime');
            $checkstart = $this->checkdatetime($start_Time);
            $check_date = $this->checkDateTimeRoomid($room_id,$request['txtstartdatetime'],$request['txtenddatetime'],$status);
            
            $checkresume    = $this->Resume($status);
            $start_Time = $request->input('txtstartdatetime');
            $end_Time = $request->input('txtenddatetime');
            $checkstartbetween = $this->checkstartbetween($start_Time, $end_Time);
            if(empty($check_date)){
                if($checkresume == 'Reject'){
           
                }
                $data = array('room_id' => $room_id,
                'name'  =>$name,
                'status' => $status,
                'start_Time' => $start_Time,
                'end_Time' => $end_Time,
                'Description' => $Description
            );
            DB::table('booking_tb')->where('br_id','=',$br_id)->update($data); 
        }else{
        
            throw new  \Exception("ไม่สามารถจองได้ห้องถูกจองแล้วกรุณาเลือกวันและเวลาที่จะจองใหม่");
        }
            
            return \Response::json(['message' => 'อัพเดทข้อมูลการจองใหม่แล้ว'], 200);
        }catch (\Exception $e) {
    
            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
            return \Response::json(['message' => $e->getMessage()], 500);
        }
        
    }
    
    private function checkroomid($room_id){
        
        $sql = "select * from room_detail_tb
        where room_id = \"$room_id\"";
        $dataset = DB::select($sql);
       

        return $dataset;
    }

        private function checktime($start_Time,$end_Time){
            $interval = $start_Time->diff($end_Time);
            \Log::info(json_encode($interval));
            
            if($start_Time < $end_Time){
                if($interval->invert == 0){
                    if($interval->d > 0){
                        
                    }elseif($interval->d == 0){
                        if($interval->h > 0){
                        
                        }else{
                            if($interval-> i < 30){
                                throw new \Exception("ระยะเวลาเข้าใช้ขั้นต่ำ 30 นาที");
                            }else{
                            if($interval->i >= 30){
                                
                            }
                        }
                    }
                }
            }
        }else{
            throw new \Exception("end_time ต้องมากกว่า start_time อย่างน่้อย 30 นาที");
        }
        
        }

        //เช็คเวลาปัจจุบัน
        private function checkdatetime($start_Time){
            \Log::info('checkdatetime');

            $timenow = \Carbon\Carbon::now()->timezone('Asia/Bangkok');
            \Log::info($timenow);
            
            $time2 = new \DateTime($start_Time,new \DateTimeZone('Asia/Bangkok'));
            
            $interval = $timenow->diff($time2);
            \Log::info(json_encode($interval));
            if($interval->invert == 0 ){
                
                if($interval->h == 0){
                throw new  \Exception ("กรุณาจองล่วงหน้า 1 ชั่วโมงเป็นอย่างน้อย");
                }
                }else{ 
                    throw new  \Exception("ห้ามจองย้อนเวลาปัจจุบัน");
            }
            
        }
        private function checkDateTimeRoomid($room_id,$start_Time,$end_Time,$status){
                
            $sql = "SELECT
            *
        FROM
            booking_tb
        WHERE
            room_id = \"$room_id\"
            and start_Time = \"$start_Time\"
            and  end_Time = \"$end_Time\"
            and status = \"$status\" ";
            
            \Log::info($sql);
        $dataset= DB::select($sql);


        return $dataset;

        }
        private function Resume($status){
            $sql = " SELECT status from booking_tb 
            where status = \"$status\"";
            $dataset = DB::select($sql);

            return $dataset;
        }
        private function checkstartbetween($start_Time, $end_Time){

            $checkstart = $this->sql_checkstartbetween($start_Time,$end_Time);

            if($checkstart > 1){

            }elseif($checkstart > 0){ 

                throw new  \Exception("ช่วงเวลานี้ถูกจองแล้ว");
            }
       
   }
        private function sql_checkstartbetween($start_Time,$end_Time){
            $sql = "SELECT
            COUNT(*) as checkcountstart
        FROM
            booking_tb
        WHERE
        start_Time
            
                BETWEEN \"$start_Time\"
                AND \"$end_Time\"
                OR
                end_Time
                BETWEEN \"$start_Time\"
                AND  \"$end_Time\"";
            
            $sql_checkstartbetween1 = DB::select($sql)[0]->checkcountstart;
                
            return $sql_checkstartbetween1;
        }
    
}
