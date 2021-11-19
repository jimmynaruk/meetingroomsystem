<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class EditDataUserController extends Controller
{
    public function index(Request $request)
    {   
        if(Session::get('haslogin') == 0 ){
            
            return view('login');
           
        }
        
    
        $sql = "SELECT * FROM user_tb";

        $dataset['userdetail'] = DB::select($sql);
        \Log::info($dataset);
        return view('optionuser',['dataset'=>$dataset]);
    }


    public function finddatauser(Request $request)
    {

        try{

            $user_id = $request->input('user_id');

            $sql = "SELECT
           user_id, user_email
        FROM
            user_tb 
        WHERE user_id = '$user_id'";

        $result = DB::select($sql);

            return $result;
        }catch  (\Exception $e) {

            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
            return \Response::json(['message' => $e->getMessage()], 500);
    
        }
    }

    public function EditOptionUser (Request $request)
    {
        // $data = $request->json()->all();
         // $user_id = $data['user_id'];
            // $room_id = $data['room_id'];
            // $password = $data['password'];
            // $user_email = $data['user_email'];
          
        try{
           
           
            $user_id = $request->input('userid');
            $user_email = $request->input('email');
            $password = $request->input('newpwd');
            // \Log::info("user_id: ".$user_id.
            // " password: ".$password.
            // " user_email: ".$user_email               
    
                
            $data = array('user_id' => $user_id,
            'password' => $password,
            'user_email' => $user_email
            );
            $result = DB::table('user_tb')->where('user_id','=',$user_id)->update($data);
        
            
        return \Response::json(['message' => 'อัพเดทข้อมูลส่วนตัวแล้ว'], 200);
        } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
    }
    }

    
}
