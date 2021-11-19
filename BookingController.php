<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function create (Request $request)
    {
        $data = $request->json()->all();
        $datenow = \Carbon\Carbon::now();

        //dd($data['create_date']); การ log ดูค่า
    try{
        
        $validator = \Validator::make($request->all(), [
            'room_id' => 'required',
            'user_id' => 'required',
            'startTime' => 'required',
            'endTime' => 'required',
            
        ]);
        
        $data = array(
            'room_id' => $data['room_id'], 
            'user_id' => $data['user_id'],
            'startTime' => $data['startTime'],
            'endTime' => $data['endTime'],
            'create_date' => $datenow
        );
        $result = DB::table('booking_tb')->insert($data);

        if ($validator->fails()) {
            throw new  \Exception($validator->errors());
        }

        return \Response::json(['message' => 'Save Complete'], 200);

    } catch (\Exception $e) {
            
        \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

        return \Response::json(['message' => $e->getMessage()], 500);
        
    }
  }
  public function update (Request $request)
  {   
      $data = $request->json()->all();
      $datenow = \Carbon\Carbon::now();
      try{
          $validator = \Validator::make($request->all(), [
              'br_id' => 'required',
              'room_id' => 'required',
              'startTime' => 'required',
              'endTime' => 'required',
              'user_id' => 'required'
             
          ]);
          $data = array(
            'br_id' => $data['br_id'], 
            'room_id' => $data['room_id'], 
            'startTime' => $data['startTime'],
            'endTime' => $data['endTime'],
            'user_id' => $data['user_id'],
            'reserve_date' => $datenow,
            'update_date' => $datenow
        );
        
           $result = DB::table('booking_tb')->where('br_id','=',$data)->update($data);

          if( $data == null || empty($data) || $data == "" ) {
                  throw new  \Exception(" Data not found.");
              }
        
      return \Response::json(['message' => 'Update Complete'], 200);
      
      } catch (\Exception $e) {

      \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

      return \Response::json(['message' => $e->getMessage()], 500);
  }
  }
  public function delete (Request $request)
  {   
      $data = $request->json()->all();
      try{

          $validator = \Validator::make($request->all(), [
              'br_id' => 'required'
          ]);

          $data = array(
            'br_id' => $data['br_id']
        );
        $result = DB::table('booking_tb')->where('br_id','=',$data)->delete();
     
        
          
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
        try {

            $data = $request->json()->all();

            $data = DB::table('room_detail_tb')->select('room_id', 'room_size', 'room_price', 'company_id')
            ->where('room_id', '=', $data['room_id'])
            ->get();

            if( $data == null || empty($data) || $data == "" ) {
                throw new  \Exception(" Data not found.");
            }
            return \Response::json(['message' => $data], 200);
            

        } catch (\Exception $e) {

            \Log::error('[' . __METHOD__ . '][' . $e->getFile() . '][line : ' . $e->getLine() . '][' . $e->getMessage() . ']');

            return \Response::json(['message' => $e->getMessage()], 500);
        }
    }
}
