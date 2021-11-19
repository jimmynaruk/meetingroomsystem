<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AddEmployController extends Controller
{
    

    public function index (Request $request)
    {
        if(Session::get('haslogin') == 0 ){
            
            return view('login');
           
        }

        $sql = " select * from user_tb ";
        $dataset['addem'] = DB::select($sql);

        $group_code = $request->input('group_code');
        $sql3 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
        INNER JOIN group_tb gt ON map.group_code = gt.group_code 
        WHERE map.group_code = '1'";
        $dataset['mapfunc'] = DB::select($sql3);
       
        return view('addemploy',['dataset'=>$dataset]);
    }

    public function saveEmployid (Request $request){
        try{

            $data = $request->json()->all();
        //    \Log::info("[".__METHOD__."]"."start");
       
            $user_id = $request->input('employid');
            $status = 'Not Active';
                $data = array('user_id' => $user_id,
                'status' => $status
            );
                DB::table("user_tb")->insert( $data); 

            return \Response::json(['message' => 'เสร็จสิ้น'], 200);
        
        }catch(\Exception $e){
    
            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

            return \Response::json(['message' => $e->getMessage()], 500);
            
        }
    }

}


