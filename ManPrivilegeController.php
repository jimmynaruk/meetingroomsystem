<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ManPrivilegeController extends Controller
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
        $sql = "SELECT * FROM function_tb";

        $sql2 = "SELECT * FROM group_tb";
        
        $group_code = $request->input('group_code');
        $sql3 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
		INNER JOIN group_tb gt ON map.group_code = gt.group_code 
		WHERE map.group_code = '1'";
        $dataset['mapfunc'] = DB::select($sql3);
        $dataset['func'] = DB::select($sql);
        $dataset['mapgroup'] = DB::select($sql2);
       
        return view('manageprivilege',['dataset'=>$dataset]);

    }
    
  
    public function saveMP(Request $request)
    {
        try{

            $function_id = $request->input('mpArr');
            $group_code = $request->input('group_code');
            // \Log::info($function_id);
            // \Log::info($group_code);
           
            $result = DB::table('mapfunc_tb')->where('group_code',$group_code)->delete();
            \Log::info('$result'.$result);
        //    $wdadwawad=explode(" ",$function_id);
         
        //    \Log::info(json_encode($wdadwawad));
          
            for($x = 0 ; $x < count($function_id); $x++){
            $data = array(
                'function_id' => $function_id[$x],
                'group_code' => $group_code
            );
            DB::table("mapfunc_tb")->insert( $data);
        }
         
            return \Response::json(['message' => 'บันทึก'], 200);

        }catch (\Exception $e) {
            \Log::info('$e'.$e);
            return $e->getMessage();
        }
    }

    
    }