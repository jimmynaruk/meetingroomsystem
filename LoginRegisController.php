<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LoginRegisController extends Controller
{
    public function index(Request $request)
    {   
        Session::put('haslogin',0);
       
        return view('login');

    }

    public function createUser(Request $request)
    {
            \Log::info("[".__METHOD__."]"."start");
        try{
            $data = $request->json()->all();

            $user_id = $request->input('txtemployid');
            $username = $request->input('txtusernameregis');
            $user_email  = $request->input('txtemailregis');
            $password = $request->input('txtpasswordregis');
            $status = 'Active';
            $user_id = $request->input('txtemployid');
            $username = $request->input('txtusernameregis');
            
            $checkregis = $this->checkuserid($username,$user_id);
           
            $data = array('username' => $username,
            'user_id' => $user_id,
            'password' => $password,
            'status' => $status,
            'group_code' => 2
             );
             \Log::info("username: ".$username.
             " user_id:".$user_id.
             " password:".$password);
            // \Log::info($data);
            
            $result = DB::table('user_tb')->where('user_id','=',$user_id)->update($data);
        //    DB::table("user_tb")->insert( $data);
          

            return \Response::json(['message' => 'สมัครสมาชิกเรียบร้อยแล้ว'], 200);

        }catch(\Exception $e){
        
            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

            return \Response::json(['message' => $e->getMessage()], 500);
            
        }
    }
    public function checkuserid($username,$user_id){
           
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
                    $check_user_name = $this->check_username($username);
                }
                


    }
    public function check_username($username){


    $checkusername_sql =  $this->sql_check_username($username);
    if($checkusername_sql){

        throw new  \Exception("Username นี้ถูกใช้แล้ว");

    }else{
      
    }

 }
    public function sql_check_username($username){
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
    
    public function checkusernamepassword(Request $request){
        \Log::info("[".__METHOD__."]"."start");
      try{
       
        $username = $request->input('loginusername');
        $password = $request->input('loginpassword');
        
        $check_username_password =  $this->usernamepassword($username,$password);
        
        
       
        if(empty($check_username_password)){
            Session::put('haslogin',0);
            throw new  \Exception("ไม่พบบัญชีผู้ใช้");
           
        }
       $func_id = $this->getgroupmenu($check_username_password[0]->group_code);
       $func_menu = $this->getfuncmenu($func_id);


       $chk_groupCode = DB::table('user_tb')->select('group_code')->where('username',$username)->first();
       
        Session::put('getuser',$username);
        Session::put('haslogin',1);
        Session::put('menu',$func_menu);
        Session::put('chk_groupCode',$chk_groupCode);
        
        if(Session::get('haslogin') == 0 ){
            
            return Redirect::to('login_register');
           
        }
        return \Response::json(['message' => "Success"], 200);
        }catch(\Exception $e){
    
            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

            return \Response::json(['message' => $e->getMessage()], 500);
            
        }

    }


    public function usernamepassword($username,$password)
    {

        $sql = "select * from user_tb
            where username = \"$username\"
            and password = \"$password\"";
            
        $dataset = DB::select($sql);

        return $dataset;
    }




    public function checkemployid($user_id)
    {

        $sql = "select * from user_tb
            where user_id = \"$user_id\"";
            
        $dataset = DB::select($sql);

        return $dataset;
    }

    public function getgroupmenu($group_code)
    {

        $sql = "SELECT * from mapfunc_tb WHERE group_code = '$group_code'";
            
        $dataset = DB::select($sql);

        return $dataset;
    }

    public function getfuncmenu($function_id)
    {   
        $id = "";
        for($i=0; $i<count($function_id); $i++){

            if($i>=1){
                $id .= ",";
            }
            $id .= $function_id[$i]->function_id;

        }

        $sql = "SELECT * from function_tb WHERE function_id in ($id)";
       
        $dataset = DB::select($sql);

        return $dataset;
    }

    



    
}
