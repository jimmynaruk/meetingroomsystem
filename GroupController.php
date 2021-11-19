<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
class GroupController extends Controller
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
        
        
        $sql = "SELECT * FROM group_tb";

        $dataset['typegroup'] = DB::select($sql);
        
        $group_code = $request->input('group_code');
        $sql3 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
		INNER JOIN group_tb gt ON map.group_code = gt.group_code 
		WHERE map.group_code = '1'";
        $dataset['mapfunc'] = DB::select($sql3);
        \Log::info($dataset);
         return view('addgroup',['dataset'=>$dataset]);

    }


    public function saveGroup (Request $request){
        
        try{
      
            $datenow = \Carbon\Carbon::now();
            $group_name =$request->input('txtgroupname');
            $create_date =$request->input('txtcreatedate');
    
                $data = array('group_name'  =>$group_name,
                'create_date' =>$datenow
                  );
                DB::table("group_tb")->insert( $data);
   
            return \Response::json(['message' => 'สร้าง Group เพิ่มแล้ว'], 200);
        
        }catch(\Exception $e){
    
            
            return $e->getMessage();
        }
      
        }

        public function Deletegroup (Request $request)
        {
        try{
            $group_code = $request->input('group_code');
        
           $result = DB::table('group_tb')->where('group_code',$group_code)->delete();
       
           if( $result == null || empty($result) || $result == "" ) {
            throw new  \Exception(" Data not found.");
        }
       return \Response::json(['message' => 'Delete Complete'], 200);
        } catch (\Exception $e) {

       \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

       return \Response::json(['message' => $e->getMessage()], 500);

        }
   }
   
   public function updateGroup (Request $request)
   {
       // $data = $request->json()->all();
        // $group_code = $data['group_code'];
        // $group_name = $data['group_name'];
          
       try{
          
          
           $group_code = $request->input('txtgroupcodeedit');
           $group_name = $request->input('txtgroupname2');

           // \Log::info("group_code: ".$group_code.
           // " group_name: ".$group_name);               
   
               
           $data = array(
           'group_name' => $group_name
           );
           $result = DB::table('group_tb')->where('group_code','=',$group_code)->update($data);
       
           
       return \Response::json(['message' => 'อัพเดทข้อมูลแล้ว'], 200);
       } catch (\Exception $e) {

       \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

       return \Response::json(['message' => $e->getMessage()], 500);
   }
   }
   public function findUserGroup(Request $request)
    {

        try{

            $group_code = $request->input('group_code');

            $sql = "SELECT
           group_code, group_name
        FROM
            group_tb 
        WHERE group_code = '$group_code'";

        $result = DB::select($sql);

            return $result;
        }catch  (\Exception $e) {

            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
            return \Response::json(['message' => $e->getMessage()], 500);
    
        }
    }

}


