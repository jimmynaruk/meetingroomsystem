<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
Use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiController extends Controller
{
   
    //จองห้องประชุม
    public function saveDetailBook (Request $request){
        try{

            $data = $request->json()->all();
        //    \Log::info("[".__METHOD__."]"."start");
       
            $room_id = $data['room_id'];
            $name  = $data['name'];
            $company_id = $data['company_id'];
            $tel = $data['tel'];
            $email = $data['email'];
            $status = 'W';
            $Description = $data['Description'];
            $start_Time = new \DateTime($data['start_Time']);
            $end_Time = new \DateTime($data['end_Time']);
            
            \Log::info($data);
            $chack_room = $this->checkroomid($room_id);
            \Log::info($chack_room);
            if(empty($chack_room)){
             throw new \Exception("ไม่มีห้องนี้");
            }
            $checktime1 = $this->checktime($start_Time,$end_Time);
            $start_Time = $data['start_Time'];
            $checkstart = $this->checkdatetime($start_Time);
            $password = $this->genpass();
            $check_date = $this->checkDateTimeRoomid($room_id,$data['start_Time'],$data['end_Time'],$status);
            $checkresume    = $this->Resume($status);
            if(empty($check_date)){
                if($checkresume == 'R'){
           
                }
                $data = array('room_id' => $room_id,
                'name'  =>$name,
                'tel'      => $tel,
                'email'  => $email,
                'status' => $status,
                'start_Time' => $start_Time,
                'end_Time' => $end_Time,
                'Description' => $Description,
                'RoomPassword' => $password,
                'company_id' => $company_id
            );
                DB::table("booking_tb")->insert( $data); 
            }else{
            
                throw new  \Exception("ไม่สามารถจองได้ห้องถูกจองแล้วกรุณาเลือกวันและเวลาที่จะจองใหม่");
            }
            
            return \Response::json(['message' => 'จองห้องประชุมเรียบร้อยรอการอนุมัติ'], 200);
        
        }catch(\Exception $e){
    
            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

            return \Response::json(['message' => $e->getMessage()], 500);
            
        }
    }
    //เช็คสถานะ
    private function Resume($status){
        $sql = " SELECT status from booking_tb 
        where status = \"$status\"";
        $dataset = DB::select($sql);

        return $dataset;
    }

    public function checkstartbetween(Request $request){
        // \Log::info($request);
        try{
            $data = $request->json()->all();
            $start_Time = $data['start_Time'];
            $end_Time = $data['end_Time'];
            $checkstart = $this->sql_checkstartbetween($start_Time,$end_Time);

            if($checkstart > 1){

            }elseif($checkstart > 0){ 

                throw new  \Exception("ช่วงเวลานี้ถูกจองแล้ว");
            }
            throw new  \Exception("จองได้");
            }catch(\Exception $e){
                \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

                return \Response::json(['message' => $e->getMessage()], 500);
                
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


  
   
    //เช็คเวลาการจองจองขั้นต่ำ5นาทีและวันที่เริ่มจองต้องมากกว่าวันที่เลิกใช้
    private function checktime($start_Time,$end_Time){
            $interval = $start_Time->diff($end_Time);
            \Log::info(json_encode($interval));
            
            if($start_Time < $end_Time){
                if($interval->invert == 0){
                    if($interval->d > 0){
                        
                    }elseif($interval->d == 0){
                        if($interval->h > 0){
                        
                        }else{
                            if($interval->i < 5){
                                throw new \Exception("ระยะเวลาเข้าใช้ขั้นต่ำ 5 นาที");
                            }else{
                            if($interval->i >= 5){
                                
                            }
                         }
                    }
                }
            }
        }else{
            throw new \Exception("end_time ต้องมากกว่า start_time อย่างน่้อย 5 นาที");
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


    //เช็คห้องว่าง-ห้องไหนถูกจองวันเวลาไหนบ้าง
    public function checkemptyroom(Request $request){
        try{

            $data = $request->json()->all();
            $start_Time = $data['start_Time'];
            $end_Time = $data['end_Time'];
            $company_id = $data['company_id'];
           
            $sql = "SELECT
            room_id,
            'empty' AS `status`,
            '' AS start_Time,
            '' AS end_Time,
            '' AS company_id
        FROM
            room_detail_tb
        WHERE
            room_id NOT IN (
                SELECT
                    room_id
                FROM
                    booking_tb
                WHERE
                    start_Time >= \"$start_Time\"
                AND end_Time <=  \"$end_Time\"
                AND company_id = $company_id
            )
        UNION
            (
                SELECT
                    room_id,
                    'not empty' AS `status`,
                    start_Time,
                    end_Time,
                    company_id

                FROM
                    booking_tb
                WHERE
                    start_Time >= \"$start_Time\"
                AND end_Time <= \"$end_Time\"
                AND company_id = $company_id
            )
        
        
        ORDER BY room_id" ;

        $dataset = DB::select($sql);
        
        return $dataset;

            
        
        }catch(\Exception $e){
    
            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
            return \Response::json(['message' => $e->getMessage()], 500);
            
        }
    }

    //อนุมัติ/ไม่อนุมัติ
    public function acceptreject(Request $request){
        try{

            $data = $request->json()->all();
        //    \Log::info("[".__METHOD__."]"."start");
          
            $br_id = $data['br_id'];
            $status = $data['status'];
            
            \Log::info($data);
            
            $chack_brid = $this->checkbrid($br_id);
            if(empty($chack_brid)){
                throw new \Exception("ไม่มีรหัสจอง");
               }
            if($status == 'A'){
               
            }elseif($status == 'R'){
            
               
                
            }else{
                throw new \Exception("ไม่มีสถานะนี้");
            }
             $data = array(
             'status' => $status
          );
        
          DB::table("booking_tb")->where('br_id','=',$br_id)->update($data); 
       
            return \Response::json(['message' => 'อัพเดทสถานะการจองแล้ว'], 200);
        
        }catch(\Exception $e){
    
            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
            return \Response::json(['message' => $e->getMessage()], 500);
            
        }
    }
    

    //รายการจองที่อนุมัติแล้ว
    public function acceptbook(Request $request){
        $status = 'A';
        $sql = " SELECT * from booking_tb 
        where status = \"$status\"";
        $dataset = DB::select($sql);

        return $dataset;
    }
    //รายการจองที่เราอนุมัติ/ไม่อนุมัติ
    public function listwaitapprove(Request $request){

        
        $status = 'W';
        $sql = " SELECT * from booking_tb 
        where status = \"$status\"";
        $dataset = DB::select($sql);

        return $dataset;

    }
    //ดูสถานะห้อง
    public function roomstatus(Request $request){

        $data = $request->json()->all();
        $status = $data['status'];
        $sql = "select * from room_detail_tb
        where status = \"$status\"";

        $dataset = DB::select($sql);

        return $dataset;
    }
    //เช็ครหัสการจอง
    private function checkbrid($br_id){
        $sql = "select * from booking_tb
        where br_id = \"$br_id\"";
        $dataset = DB::select($sql);

        return $dataset;
      
    }
    private function checkroomid($room_id){
        
            $sql = "select * from room_detail_tb
            where room_id = \"$room_id\"";
            $dataset = DB::select($sql);
           
    
            return $dataset;
       
      
    }
    //ลบห้อง
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
            $result = DB::table('room_detail_tb')->where('room_id','=',$room_id)->Deletes($data);
            
            if( $data == null || empty($data) || $data == "" ) {
            throw new  \Exception(" Data not found.");
        }
       
        return \Response::json(['message' => 'Delete Complete'], 200);
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

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

    
    //ลบห้อง
    public function deleteRoom (Request $request)
    {   
        try{
            \Log::info("start ");
           
            $room_id = $request->input('room_id');
            \Log::info("room_id: ".$room_id);
            $result = DB::table('room_detail_tb')->where('room_id',$room_id)->delete();
            
            
            if( $result == null || empty($result) || $result == "" ) {
            throw new  \Exception(" Data not found.");
        }
        
        return \Response::json(['message' => 'Delete Complete'], 200);
        
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

    }
    
    }

    //แก้ไขห้องประชุม
    public function updateDetailRoom (Request $request)
    {

        $data = $request->json()->all();
        \Log::info("[".__METHOD__."]"."start");
        try{
            $room_id = $data['room_id'];
            $room_size = $data['room_size'];
            $company_id = $data['company_id'];
            $status = $data['status'];
            $Description = $data['Description'];
            $datenow = \Carbon\Carbon::now();
            $update_by = $data['update_by'];
            
                $data = array( 
                
                'room_size' => $room_size,
                'company_id'  =>$company_id,
                'Description' => $Description,
                'update_date' => $datenow,
                'update_by' => $update_by,
                'status' => $status
                  );
        
                  \Log::info($data);
       
                  
            $result = DB::table('room_detail_tb')->where('room_id','=',$room_id)->update($data);
            
        return \Response::json(['message' => 'อัพเดทข้อมูลห้องประชุมแล้ว'], 200);
        } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
    }
    }

    //สร้างห้องประชุม
    public function savecreateRoom (Request $request){

        $data = $request->json()->all();
        \Log::info("[".__METHOD__."]"."start");
        try{
        
            $room_size = $data['room_size'];
            $company_id = $data['company_id'];
            $Description = $data['Description'];
            $status = 'A';
            $datenow = \Carbon\Carbon::now();

                     $data = array('room_size' => $room_size,
                         'company_id'  =>$company_id,
                         'Description' => $Description,
                         'create_date' => $datenow,
                         'status' => 'A'
            );
                
            \Log::info($data);
          
            DB::table("room_detail_tb")->insert( $data);
            
            return \Response::json(['message' => 'สร้างห้องประชุมแล้ว'], 200);
        
        }catch(\Exception $e){
    
            return $e->getMessage();
            
        }
      
        }
        //อัพเดทสถานะการจอง
        public function updatedetailBooking (Request $request)
        {
            $data = $request->json()->all();
            try{
           
            $br_id = $data['br_id'];
            $datenow = \Carbon\Carbon::now();
            $room_id = $data['room_id'];
            $name  = $data['name'];
            $company_id = $data['company_id'];
            $tel = $data['tel'];
            $email = $data['email'];
            $Description = $data['Description'];
            $start_Time = new \DateTime($data['start_Time']);
            $end_Time = new \DateTime($data['end_Time']);
            $status = $data['email'];
            \Log::info($data);
            $chack_room = $this->checkroomid($room_id);
            \Log::info($chack_room);
            if(empty($chack_room)){
             throw new \Exception("ไม่มีห้องนี้");
            }
            $checktime1 = $this->checktime($start_Time,$end_Time);
            $start_Time = $data['start_Time'];
            $checkstart = $this->checkdatetime($start_Time);
            $password = $this->genpass();
            $check_date = $this->checkDateTimeRoomid($room_id,$data['start_Time'],$data['end_Time'],$status);
            $checkresume    = $this->Resume($status);
            if(empty($check_date)){
                if($checkresume == 'R'){
           
                }
                $data = array('room_id' => $room_id,
                'name'  =>$name,
                'tel'      => $tel,
                'email'  => $email,
                'start_Time' => $start_Time,
                'end_Time' => $end_Time,
                'Description' => $Description,
                'company_id' => $company_id
            );
            $result = DB::table('booking_tb')->where('br_id','=',$br_id)->update($data); 
            }else{
            
                throw new  \Exception("ไม่สามารถจองได้ห้องถูกจองแล้วกรุณาเลือกวันและเวลาที่จะจองใหม่");
            }
                
            return \Response::json(['message' => 'อัพเดทข้อมูลการจองใหม่แล้ว'], 200);
            } catch (\Exception $e) {
    
            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
            return \Response::json(['message' => $e->getMessage()], 500);
        }
    }
  //ยกเลิกการจอง
        public function Deletedetailbook (Request $request)
    {   
            
        try{
             $br_id = $request->input('br_id');


            
        $result = DB::table('booking_tb')->where('br_id',$br_id)->delete();

        $sql = "select * from booking_tb";
        $dataset = DB::select($sql);

        return \Response::json(['message' => 'Delete Complete','dataset'  =>$dataset], 200);
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

    }
    }
    //เช็คห้องทั้งหมด
    public function checkRoom(Request $request){
        try{
          

       $sql = "select * from room_detail_tb";
       $dataset = DB::select($sql);

       return \Response::json(['dataset'  =>$dataset], 200);
        } catch (\Exception $e) {

       \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

       return \Response::json(['message' => $e->getMessage()], 500);

   }

    }

    //เช็ครหัสผ่าน
    public function checkuserRoomPassword(Request $request){
        \Log::info("[".__METHOD__."]"."start");
            $data = $request->json()->all();

                $br_id = $data['br_id'];
                $RoomPassword = $data['RoomPassword'];
      try{
     
        $check_status_room =  $this->userRoompassword($br_id,$RoomPassword);

            if ($check_status_room >0) {

            $sql = "select count(*) as count_rows  from booking_tb
            where  br_id = \"$br_id\"
            and RoomPassword = \"$RoomPassword\"";

            $check_password_room = DB::select($sql)[0]->count_rows;

                if ($check_password_room > 0 ) { 
                    return \Response::json(['message' => "Success"], 200);
                } else {
                    throw new  \Exception("รหัสผ่านไม่ถูกต้อง");
                }
                }
            else {
                throw new  \Exception("ไม่ได้อนุมัติไม่อนุญาติให้ใช้");
            }
           
            }catch(\Exception $e){
        
                \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

                return \Response::json(['message' => $e->getMessage()], 500);
                
            }

    }


    public function checkuserid(Request $request){
        try{
        \Log::info("[".__METHOD__."]"."start");
            $data = $request->json()->all();
               $username = $data['username'];
               $user_id = $data['user_id'];
               $check_user_id = $this->check_user_id($user_id);
               $check_status_userid = $this->checkuseridstatus($user_id);
               if ($check_status_userid >1) {

                $sql = "select count(*) as checkcountstatus  from user_tb
                where  user_id = \"$user_id\"
                and status = 'Not Active' ";
    
                $check_status_userid = DB::select($sql)[0]->count_rows;
               }
                if($check_status_userid == 1){
                    throw new  \Exception("รหัสพนักงานนี้สมัครใช้งานระบบไปแล้ว");
                    
                }else{
                
                }
                $check_user_name = $this->check_username($username);

               

            }catch(\Exception $e){
        
                \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

                return \Response::json(['message' => $e->getMessage()], 500);
                
            }

    }
    private function check_username($username){


    $checkusername_sql =  $this->sql_check_username($username);
    if($checkusername_sql){

        throw new  \Exception("Username นี้ถูกใช้แล้ว");

    }else{
        throw new  \Exception("สามารถใช้ Username นี้ได้");
    }

 }
    private function sql_check_username($username){
        $sql = "select username from user_tb
        where username = \"$username\"";
        $dataset= DB::select($sql);

        return $dataset;
    }
    private function check_user_id($user_id){

           $check_user_num =  $this->checkemptyuserid($user_id);
        if(empty($check_user_num)){

            throw new  \Exception("ไม่พบรหัสพนักงานนี้");

        }
    }

    private function checkemptyuserid($user_id)
    {

        $sql = "select user_id  from user_tb
            where  user_id = \"$user_id\"";
            $dataset= DB::select($sql);
    

            return $dataset;
       
    }

    private function checkuseridstatus($user_id)
    {

        $sql = "select count(*) as checkcountstatus  from user_tb
            where  user_id = \"$user_id\"
            and status = 'Active'";

        $check_status_userid = DB::select($sql)[0]->checkcountstatus;

        return $check_status_userid;
       
    }

    private function userRoompassword($br_id,$RoomPassword)
    {

        $sql = "select count(*) as checkcountstatus  from booking_tb
            where  br_id = \"$br_id\"
            and status = 'A'";

        $check_status_room = DB::select($sql)[0]->checkcountstatus;

        return $check_status_room;
       
    }

    //genqr base64
    public function genarateqr(Request $request){

        
        $password = $request->input('RoomPassword');

        
        $qrCode = new QrCode($password);
        
        header('Content-Type: '.$qrCode->getContentType());
         $qrCode->writeString();
      \Log::info($qrCode->writeDataUri());
        return $qrCode->writeDataUri();
        

    }
    //gen รหัสห้อง
    private function genpass(){

        $genqr = str_random(10);

        return $genqr;

    }

    //ดึงการจองที่อนุมัติแล้วขึ้นปฏิทิน
    public function getTime(){
        \Log::info("[".__METHOD__."]"."start");
        
        $sql = "SELECT
        name,
        start_Time,
        end_Time,
        Description,
        room_id
        FROM
        booking_tb
        where status = 'A'";
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
            }else{

                $eventsColor = 'd';
            }
            
      
        $result[]=array(  
            "start" =>$data->start_Time,
            "end" =>$data->end_Time,
            "title"=>$data->Description." ห้อง".$data->room_id,
            // กำหนด event object property อื่นๆ ที่ต้องการ
            "resourceId"=> $eventsColor
        );    
    }
        \Log::info("result".json_encode($result));
        return $result;


    }
    

    public function history(Request $request){
        try{
          

            $sql = "select * from room_detail_tb";
            $dataset = DB::select($sql);
     
            return \Response::json(['dataset'  =>$dataset], 200);
             } catch (\Exception $e) {
     
            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
     
            return \Response::json(['message' => $e->getMessage()], 500);
     
        }
        
    }

    //ประวัติห้องใช้ room_id หา
    public function historyroomid(Request $request){
        $data = $request->json()->all();
        $room_id = $data['room_id'];

        $sql = "select * from room_detail_tb  
        where room_id = \"$room_id\" ";
        $dataset = DB::select($sql);

        return $dataset;

    }
    

    public function fakedelete(Request $request){
        $data = $request->json()->all();
        \Log::info("[".__METHOD__."]"."start");
        try{
            $datenow = \Carbon\Carbon::now();
            $room_id = $data['room_id'];

            $checkroomidfakedelete = $this->checkroomidfakedalete($room_id);
            if(!empty($checkroomidfakedelete)){
                $data = array( 
                'update_date' =>$datenow,
                'status' => 'D'
                  );
                  \Log::info('1');
                }
                  \Log::info($data);
                  
            $result = DB::table('room_detail_tb')->where('room_id','=',$room_id)->update($data);
          
        return \Response::json(['message' => 'ลบห้องประชุมแล้ว'], 200);
        } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
    }
    }

    private function checkroomidfakedalete($room_id){
        \Log::info('checkroomidfakedalete');
        $sql = "select * from room_detail_tb
        where room_id = $room_id" ;
        $dataset = DB::select($sql);
        \Log::info($dataset);
        return $dataset;
    }


}


