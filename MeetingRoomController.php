<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class MeetingRoomController extends Controller
{
    //
    public function create (Request $request)
    {
        $data = $request->json()->all();
        $datenow = \Carbon\Carbon::now();
        try {
            
            $validator = \Validator::make($request->all(), [
                
                'room_size' => 'required',
                'room_price' => 'required',
                'company_id' => 'required',
                'status'    => 'required',
                'create_date' => 'required'
                
                
            ]);
            $data = array(
                
                'room_size' => $data['room_size'],
                'room_price' => $data['room_price'],
                'company_id' => $data['company_id'],
                'status'    => $data['status'],
                'create_date' => $datenow
               
            );
            $result = DB::table('room_detail_tb')->insert($data);
    
            if ($validator->fails()) {
                throw new  \Exception($validator->errors());
            }
            
            if( $result == 1) {
                return \Response::json(['message' => 'OK'], 200);
            }

            throw new  \Exception("Insert Data not found.");

        } catch (\Exception $e) {

            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

            return \Response::json(['message' => $e->getMessage()], 500);
        }

    }

    public function update (Request $request)
    {
        try{
            $validator = \Validator::make($request->all(), [
                'room_id' => 'required',
                'room_size' => 'required',
                'room_price' => 'required',
                'company_id' => 'required',
                
            ]);
    
            if( $data == null || empty($data) || $data == "" ) {
                    throw new  \Exception(" Data not found.");
                }
            $data = $request->json()->all();
    
            $data = array(
                'room_id' => $data['room_id'], 
                'room_size' => $data['room_size'],
                'room_price' => $data['room_price'],
                'company_id' => $data['company_id'],
                

            );
            $result = DB::table('room_detail_tb')->where('room_id','=',$data)->update($data);
            
        return \Response::json(['message' => 'Update Complete'], 200);
        } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
    }
    }

    public function Delete (Request $request)
    {   
        $data = $request->json()->all();
        try{

            $validator = \Validator::make($request->all(), [
                'room_id' => 'required'
    
            ]);
          
            $data = array(
                'room_id' => $data['room_id']
               
            );
            //$result = DB::table('room_detail_tb')->truncate($data);
            $result = DB::table('room_detail_tb')->where('room_id','=',$data)->delete();
            
            if( $data == null || empty($data) || $data == "" ) {
            throw new  \Exception(" Data not found.");
        }
       
        return \Response::json(['message' => 'Delete Complete'], 200);
         } catch (\Exception $e) {

        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);

    }
    }

    public function index (Request $request)
    {
      

            $data = $request->json()->all();
            $sql = "SELECT room_id,room_size,room_price,cpn.namecom,status,create_date 
            FROM room_detail_tb rdt
            INNER JOIN company_tb cpn
            ON rdt.company_id = cpn.company_id";

            $dataset['datalist'] = DB::select($sql);
            $dataset['detataillist'] = array('หมายเลขห้องประชุม', 'ขนาดห้อง', 'ราคามัดจำ','บริษัท',
            'สถานะประชุม','วันที่สร้างห้อง');
            
            return view('detailmeetingroom',['dataset'=>$dataset]);

    }

    public function saveRoom (Request $request){
    try{
        \Log::info("[".__METHOD__."]"."start");
        $room_size  = $request->input('txtcap');
        $room_price = $request->input('txtprice');
        $namecom    = $request->input('txtcpn');
        $company_id = $request->input('txtcpnid');
        $status       = $request->input('txtStatus');
        $create_date = $request->input('txtcreate');

        \Log::info("room_size: ".$room_size.
                    " room_price: ".$room_price.
                    " namecom: ".$namecom.
                    " company_id: ".$company_id.
                    " status:".$status.
                    " create_date: ".$create_date );


                $data = array('room_size'  =>$room_size,
                      'room_price'      => $room_price,
                      'namecom'         => $namecom,
                      'company_id'      => $company_id,
                      'status'          => $status,
                      'create_date'     =>  $create_date 
                        );
                        
        db::table("room_detail_tb")->insert( $data);
        return \Response::json(['message' => 'Create Complete'], 200);
    }catch(\Exception $e){

        return $e->getMessage();
        
    }
  
    }
  

}
