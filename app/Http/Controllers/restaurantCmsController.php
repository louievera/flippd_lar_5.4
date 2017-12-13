<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\cmsModel\restaurant;
use Validator;
use DB;

class restaurantCmsController extends Controller
{
    public function viewList()
    {
      $results = restaurant::getRestaurant();
      return view("restaurantList",["results"=>$results]);
    }

    public function view($id)
    {
      $res = restaurant::getRestaurantInfo($id);

      $data = ["result"=>$res];

      return view("restaurantView",$data);
    }

    public function verifyRestorant($id)
    {

      $res = restaurant::verifyResto($id);

      // return view("restaurantView");
      return redirect("restaurant/list");
    }

    public function insertUser($id,$token, $business_id)
    {
        $data =DB::table('business_owner')->find($id);

        $emailuser = explode("@",$data->email);
        $date = date("mdy");
        $username = $emailuser[0].$date;
        $password = str_random(8);
        $input = ["user"=>$username, "password"=>$password, "id" => $id, "token"=> $token, "business_id" => $business_id];
        restaurant::insertUserOwner($input);

        session()->put('success','Owner verified');

        return redirect("cms/list");
    }
}
