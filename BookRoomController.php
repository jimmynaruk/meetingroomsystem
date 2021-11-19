<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class BookRoomController extends Controller
{
    

    public function index (Request $request)
    {   
        $chkGroup = Session::get('chk_groupCode')->group_code;

        if ($chkGroup === 1) {
            return redirect('main');
        }
        if(Session::get('haslogin') == 0 ){
            
            return view('login');
           
        }
       
        $data = $request->json()->all();
        $sql = "SELECT rdt.room_id,rdt.room_size,cpn.namecom ,rdt.create_date,Description FROM room_detail_tb rdt
        INNER JOIN company_tb cpn ON rdt.company_id = cpn.company_id ";

        $dataset['datalist'] = DB::select($sql);
    
        return view('book_room',['dataset'=>$dataset]);
    }

    public function index2 (Request $request)
    {
        $group_code = $request->input('group_code');
        $sql3 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
        INNER JOIN group_tb gt ON map.group_code = gt.group_code 
        WHERE map.group_code = '1'";
        $dataset['mapfunc'] = DB::select($sql3);
    
        return view('book_room',['dataset'=>$dataset]);
    }
 
    

    public function totalamountperday (Request $request){
        try{

            $data = $request->json()->all();
        //    \Log::info("[".__METHOD__."]"."start");
        
            $room_id = $request->input('txtroomiddetail');
            $name  = $request->input('txtnamedetail');
            $tel = $request->input('txtteldetail');
            $email = $request->input('txtemaildetail');
            $status = 'Wait';
            $Description = $request->input('txtdescriptiondetail');
            $start_Time = new \DateTime($request['txtstartdatetime']);
            $end_Time = new \DateTime($request['txtenddatetime']);
            
            
            $chack_room = $this->checkroomid($room_id);
            if(empty($chack_room)){
             throw new \Exception("ไม่มีห้องนี้");
            }
           
            $checktime1 = $this->checktime($start_Time,$end_Time);
            $start_Time = $request->input('txtstartdatetime');
            $checkstart = $this->checkdatetime($start_Time);
            $password = $this->genpass();
           
            $start_Time = $request->input('txtstartdatetime');
            $end_Time = $request->input('txtenddatetime');
            $checkstartbetween = $this->checkstartbetween($start_Time,$end_Time);
            $checkresume = $this->Resume($status);
            if($checkresume == 'Reject'){
            }
            $data = array('room_id' => $room_id,
            'name'  =>$name,
            'tel'      => $tel,
            'email'  => $email,
            'status' => $status,
            'start_Time' => $start_Time,
            'end_Time' => $end_Time,
            'Description' => $Description,
            'RoomPassword' => $password
        );
    
        DB::table("booking_tb")->insert($data); 
        
        return \Response::json(['message' => 'จองห้องประชุมเรียบร้อยรอการอนุมัติ'], 200);
    
        
        }catch(\Exception $e){
    
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
                if($interval->d == 0){
                    if($interval->h == 0){
                        throw new  \Exception ("กรุณาจองล่วงหน้า 1 ชั่วโมงเป็นอย่างน้อย");
                    }
                   
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
        public function Delete (Request $request)
    {   
        $data = $request->json()->all();
        try{

            $validator = \Validator::make($request->all(), [
                'room_id' => 'required'
    
            ]);
          
            $data = array(
                'room_id' => $room_id
               
            );
            //$result = DB::table('room_detail_tb')->truncate($data);
            $result = DB::table('room_detail_tb')->where('room_id','=',$room_id)->delete($data);
            
            if( $data == null || empty($data) || $data == "" ) {
            throw new  \Exception(" Data not found.");
        }
       
        return \Response::json(['message' => 'Delete Complete'], 200);
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

    }
        
    }
    public function indexBookroom ($id=0,Request $request)
    {   
        $chkGroup = Session::get('chk_groupCode')->group_code;

        if ($chkGroup === 1) {
            return redirect('main');
        }
        \Log::info('indexBookroom'.'id='.$id);
        $group_code = $request->input('group_code');
        $sql2 = "SELECT * FROM time_book_tb";
        $dataset['timebook'] = DB::select($sql2);
        

        $sql3 = "SELECT br_id,room_id,name,email,tel,start_Time,end_Time,Description,status FROM booking_tb 
        where room_id = $id";
        $dataset['databooking'] = DB::select($sql3);

        $sql4 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
        INNER JOIN group_tb gt ON map.group_code = gt.group_code 
        WHERE map.group_code = '1'";
        $dataset['mapfunc'] = DB::select($sql4);
        
        $dataset['save_roomid'] = $id;
        

        \Log::info(json_encode($dataset['databooking']));

        return view('detail_roomid',['dataset'=>$dataset]);

    }
    
    

    private function genpass(){

        $genqr = str_random(10);

        return $genqr;

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
        BETWEEN  \"$start_Time\"
                AND \"$end_Time\"";
            
            $sql_checkstartbetween1 = DB::select($sql)[0]->checkcountstart;
                
            return $sql_checkstartbetween1;
        }




}


