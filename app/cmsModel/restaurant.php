<?php

namespace App\cmsModel;

use Illuminate\Database\Eloquent\Model;
use DB;
class restaurant extends Model
{
    //
    public static function getRestaurant()
    {

      $res = DB::table('business as b')
                    ->selectRaw('b.id, b.name, b.description, b.address, bo.email, bo.mobile_number,IF(b.is_verified=1, "Yes","No") AS verified')
                    ->leftJoin('business_owner as bo', 'b.business_id', 'bo.business_id')
                    ->orderBy('is_verified','ASC')
                    ->paginate(10);

      return $res;
    }

    public static function getRestaurantInfo($id)
    {

      $res = DB::table('business_owner as bo')
                    ->selectRaw('b.business_id, bo.name as claimer, bo.id, IF(b.is_verified=1, "Yes","No") AS verified,b.address, b.name, b.description, bo.email, bo.mobile_number, bo.document')
                    ->leftJoin('business as b', 'b.business_id', 'bo.business_id')
                    ->where('b.id', $id)
                    ->first();
      return $res;
    }

    public static function verifyResto($id)
    {
      DB::table('business')->where("id",$id)->update(["verified"=>1]);
    }

    public static function insertUserOwner($input)
    {
      $table = DB::table('business_owner');
      $data = ['user_name'        =>$input['user'],
              "password"          => bcrypt($input['password']),
              "field"             => $input['password'],
              "token"             => $input['token']
               ];
      $table->where("id",$input['id'])->update($data);


      $businessData = ['is_verified' => 1, "user_id" => $input['id'] ];
      DB::table('business')
                ->where("business_id",$input['business_id'])
                ->update($businessData);
    }

    public static function getUserInfo($email)
    {
      $res = DB::table('users')->where('email',$email);
      return $res;
    }
}
