<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ManageuserController extends Controller
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
        
        $sql = "SELECT
        utb.user_id,
        utb.username,
        utb.password,
        gtb.group_name
    FROM
        user_tb utb
    INNER JOIN group_tb gtb ON utb.group_code = gtb.group_code";

        $dataset['dataacc'] = DB::select($sql);
        
        $sql2 = "select * from group_tb";
        $dataset['datagroup'] = DB::select($sql2);
        
        $group_code = $request->input('group_code');
        $sql3 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
		INNER JOIN group_tb gt ON map.group_code = gt.group_code 
		WHERE map.group_code = '1'";
        $dataset['mapfunc'] = DB::select($sql3);

        return view('manageuser',['dataset'=>$dataset]);

    }

    public function EditUser (Request $request)
    {

        $data = $request->json()->all();
        // \Log::info("[".__METHOD__."]"."start");
        try{
            // $user_id = $data['txtuseridedit'];
            // $username = $data['usernameedit'];
            // $password = $data['passwordedit'];
            // $group_code = $data['groupcodeedit'];


            $user_id =$request->input('txtuseridedit');
            $username =$request->input('usernameedit');
            $password =$request->input('passwordedit');
            $group_code =$request->input('groupcodeedit');
                $data = array( 
                'user_id' => $user_id,
                'username'  =>$username,
                'password' => $password,
                'group_code' => $group_code
                  );
        
                //   \Log::info($data);
       
                  
            $result = DB::table('user_tb')->where('user_id','=',$user_id)->update($data);
            
        return \Response::json(['message' => 'แก้ไขข้อมูลผู้ใช้แล้ว'], 200);
        } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
    }
    }
    
    public function findDetailUser(Request $request)
    {

        try{

            $user_id = $request->input('user_id');

            $sql = "SELECT
            utb.user_id,
            utb.username,
            utb.password,
            gtb.group_code
        FROM
            user_tb utb
        INNER JOIN group_tb gtb ON utb.group_code = gtb.group_code
        WHERE
            utb.user_id = '$user_id'
            ORDER BY utb.group_code";

        $result = DB::select($sql);

        return $result;
        }catch  (\Exception $e) {

            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
            return \Response::json(['message' => $e->getMessage()], 500);
    
        }
    }

    public function deleteAccUser (Request $request)
    {   
        try{
            \Log::info("start ");
           
            $user_id = $request->input('user_id');
            \Log::info("user_id: ".$user_id);
            $result = DB::table('user_tb')->where('user_id',$user_id)->delete();
            
            
            if( $result == null || empty($result) || $result == "" ) {
            throw new  \Exception(" Data not found.");
        }
        
        return \Response::json(['message' => 'Delete Complete'], 200);
        
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

    }
    
    }
}

