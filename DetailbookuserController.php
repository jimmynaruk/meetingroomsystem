<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class DetailbookuserController extends Controller
{
    public function index(Request $request)
    {   
        if(Session::get('haslogin') == 0 ){
            
            return view('login');
           
        }
       
    
        $sql = "SELECT * FROM booking_tb where status = 'Accept'";

        $dataset['userdetailbook'] = DB::select($sql);

        // \Log::info($dataset);
        $group_code = $request->input('group_code');
        $sql3 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
        INNER JOIN group_tb gt ON map.group_code = gt.group_code 
        WHERE map.group_code = '1'";
        $dataset['mapfunc'] = DB::select($sql3);
        return view('detail_bookuser',['dataset'=>$dataset]);
    }
    

}
