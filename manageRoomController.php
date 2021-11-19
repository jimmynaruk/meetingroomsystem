<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class manageRoomController extends Controller
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
        $sql = "SELECT rdt.room_id,rdt.room_size,cpn.namecom ,rdt.create_date,rdt.Description FROM room_detail_tb rdt
        INNER JOIN company_tb cpn ON rdt.company_id = cpn.company_id ";

        $dataset['datalist'] = DB::select($sql);
        

        $sql2 = "SELECT * FROM company_tb";
        $dataset['company'] = DB::select($sql2);

        $sql3 = "SELECT * FROM room_detail_tb rdt INNER JOIN company_tb cpn ON rdt.company_id = cpn.company_id ";
        $dataset['editcom'] = DB::select($sql3);
        
        $group_code = $request->input('group_code');
        $sql4 = "SELECT
        fun.function_name,map.group_code,fun.linkfunc,gt.group_name
        FROM
        function_tb fun
        INNER JOIN mapfunc_tb map ON map.function_id = fun.function_id
		INNER JOIN group_tb gt ON map.group_code = gt.group_code 
		WHERE map.group_code = '1'";
        $dataset['mapfunc'] = DB::select($sql4);
        
        
        return view('manage_room',['dataset'=>$dataset]);

    }
    public function savecreateRoom (Request $request){

        $data = $request->json()->all();
        \Log::info("[".__METHOD__."]"."start");
        try{
            // $room_id = $data['room_id'];
            // $name  = $data['name'];
            // $tel = $data['tel'];
            
            $room_size = $request->input('roomsize');
            $company_id =$request->input('txtcpnid');
            $Description =$request->input('txtdescription');
          
                     $data = array('room_size' => $room_size,
                         'company_id'  =>$company_id,
                         'Description' => $Description
            );
                
            \Log::info($data);
          
            DB::table("room_detail_tb")->insert( $data);
            
            return \Response::json(['message' => 'สร้างห้องประชุมแล้ว'], 200);
        
        }catch(\Exception $e){
    
            return $e->getMessage();
            
        }
      
        }

    public function updateDetailRoom (Request $request)
    {

        $data = $request->json()->all();
        \Log::info("[".__METHOD__."]"."start");
        try{
            // $room_id = $data['txtroomidedit'];
            // $room_size = $data['roomsizeedit'];
            // $company_id = $data['txtcpnid1edit'];
            // $Description = $data['txtdescriptionedit'];

            $room_id =$request->input('txtroomidedit');
            $room_size =$request->input('roomsizeedit');
            $company_id =$request->input('txtcpnid1edit');
            $Description =$request->input('txtdescriptionedit');
            
                $data = array( 
                'room_size' => $room_size,
                'company_id'  =>$company_id,
                'Description' => $Description
                  );
        
                  \Log::info($data);
       
                  
            $result = DB::table('room_detail_tb')->where('room_id','=',$room_id)->update($data);
            
        return \Response::json(['message' => 'อัพเดทข้อมูลห้องประชุมแล้ว'], 200);
        } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
    }
    }
   
   
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
    
    public function finddetailroom(Request $request)
    {

        try{

            $room_id = $request->input('room_id');

            $sql = "SELECT
            rdt.room_id,
            rdt.room_size,
						cpn.namecom,
						rdt.Description
        FROM
           room_detail_tb rdt
        INNER JOIN company_tb cpn ON rdt.company_id = cpn.company_id
        WHERE rdt.room_id = '$room_id'";

        $result = DB::select($sql);

            return $result;
        }catch  (\Exception $e) {

            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');
    
            return \Response::json(['message' => $e->getMessage()], 500);
    
        }
    }
    
}


