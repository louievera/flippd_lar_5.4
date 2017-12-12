<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
use App\Restaurant;
use DB;

class RestaurantController extends Controller
{

    public function report($business_id, $startDate, $endDate)
    {
       $res = Restaurant::getReports([
          'business_id' => $business_id,
          'date_start'  => $startDate,
          'date_end'    => $endDate
       ]);

       if($res['total_rating'] > 0){

          return json_encode([
            'status'  => 200,
            'method'  => 'rating_result',
            'message' => 'Success',
            'data'    => $res
          ]);

       }
       else{

          return json_encode([
            'status'  => 204,
            'method'  => 'rating_result',
            'message' => 'Empty Result',
            'data'    => ''
          ]);
       }
   }

   public function visit($business_id, $user_id)
   {
     $ids = ["business" => $business_id, "user" => $user_id];
     $res = Restaurant::insertVisit($ids);

     if(!empty($res)) {
       return json_encode([
         'status'  => 200,
         'method'  => 'rating_result',
         'message' => 'Success',
         'data'    => $res
        ]);
      }
       else {
         return json_encode([
           'status'  => 204,
           'method'  => 'rating_result',
           'message' => 'unsuccessful',
           'data'    => $res
         ]);
       }
     }

     public function claimRestaurant(Request $request)
     {
       $validate = Validator::make($request->all(),[
         "business_name"    => "required",
         "email"            => "required|email",
         "mobile_number"    => "required|numeric",
         "document"         =>"required|mimes:jpeg,jpg,png,svg,pdf,doc,docx,md,xml,rtf,docm,dotx,dotm,wpd,wps,txt"
       ]);

         if($validate->fails())
         {
           return json_encode([
             'status'  => 204,
             'method'  => 'claim_restaurant',
             'message' => 'unsuccessful',
             'data'    => [
                         'business_name'    => $validate->errors()->first('business_name'),
                         'email'            => $validate->errors()->first('email'),
                         'document'         => $validate->errors()->first('document')
                       ],
              'email' => $request['email ']
           ]);
         }
         else {
           $res = Restaurant::insertClaimRestaurant($request);
           return json_encode([
             'status'  => 200,
             'method'  => 'claim_restaurant',
             'message' => 'Success',
             'data'    => $res
            ]);
         }
     }

     public function login(Request $request)
     {
       $validate = Validator::make($request->all(),[
                                        "username" => "required",
                                        "password" => "required"
                                    ]);
        if($validate->fails())
        {
          return json_encode([
            'status'  => 204,
            'method'  => 'restaurant_login',
            'message' => 'unsuccessful',
            'data'    => [
                        'username'  => $validate->errors()->first('username'),
                        'password'  => $validate->errors()->first('password')
             ]
          ]);
        }

          $res = Restaurant::getRestaurantOwner($request);

          if($res->count() != 0)
          {
            $path = asset('storage/restaurant_photos/'.$res[0]->image);
            $comment = explode('--',$res[0]->comment);
            $images = explode('--',$res[0]->image);

            $finalRes = [ "restaurant_name"   => $res[0]->name,
                          "business_id"       => $res[0]->business_id,
                          "description"       => $res[0]->description,
                          "thankyou_message"  => $res[0]->thankyou_message,
                          "owner_name"        => $res[0]->owner_name,
                          "user_name"         => $res[0]->user_name,
                          "email"             => $res[0]->email,
                          "comment"           => $comment,
                          "image"             => $images,
                          "is_new"            => $res[0]->is_new
                        ];
            if(password_verify($request['password'], $res[0]->password))
            {
              if($res[0]->is_new == 1)
              {
                DB::table('business_owner')
                          ->where('id',$res[0]->owner_id)
                          ->update(['is_new'=>0]);
              }
              return json_encode([
                'status'  => 200,
                'method'  => 'restaurant_login',
                'message' => 'Success',
                'data'    => $finalRes
               ]);
            }
          }

          return json_encode([
            'status'  => 204,
            'method'  => 'restaurant_login',
            'message' => 'unsuccessful',
            'data'    => ""
          ]);
      }

      public function description(Request $request, $business_id)
      {
        $validate = Validator::make($request->all(),["description" => "required"]);
        if($validate->fails())
        {
          return json_encode([
            'status'  => 204,
            'method'  => 'description',
            'message' => 'unsuccessful',
            'data'    => ["description" => $validate->errors()->first('description')]
          ]);
        }
        $ins = Restaurant::insertDescription($request, $business_id);

        return json_encode([
          'status'  => 200,
          'method'  => 'restaurant_description',
          'message' => 'Success',
          'data'    => $ins
         ]);
      }

      public function getDescription($id)
      {
        $res = Restaurant::getDescriptionInfo($id);
        return json_encode([
          'status'  => 200,
          'method'  => 'restaurant_description',
          'message' => 'Success',
          'data'    => $res
         ]);
      }

      public function thankyouMessage(Request $request, $business_id)
      {
        $validate = Validator::make($request->all(),["thankyou_message" => "required"]);
        if($validate->fails())
        {
          return json_encode([
            'status'  => 204,
            'method'  => 'thankyou_message',
            'message' => 'unsuccessful',
            'data'    => ["thankyou_message" => $validate->errors()->first('thankyou_message')]
          ]);
        }
        $ins = Restaurant::insertThankyoumessage($request, $business_id);

        return json_encode([
          'status'  => 200,
          'method'  => 'thankyou_message',
          'message' => 'Success',
          'data'    => $ins
         ]);
      }

    
      public function restaurantPhotos(Request $request)
      {
        $validate = Validator::make($request->all(),["business_id"=>"required", "image"=>"required|mimes:jpeg,jpg,png,svg"]);

        if($validate->fails())
        {
          return json_encode([
            'status'  => 204,
            'method'  => 'restaurant_photo',
            'message' => 'unsuccessful',
            'data'    => ["business_id" => $validate->errors()->first('business_id'), "image" => $validate->errors()->first('image')]
           ]);
        }

        $photoCnt = DB::table('business_photo')->where('business_id',$request['business_id'])->count();
        if($photoCnt < 4)
        {
            $res = Restaurant::setFeaturedPhoto($request);
            return json_encode([
              'status'  => 200,
              'method'  => 'restaurant_photo',
              'message' => 'Success',
              'data'    => $res
             ]);
        }

        return json_encode([
          'status'  => 204,
          'method'  => 'restaurant_photo',
          'message' => 'unsuccessful',
          'data'    => ""
         ]);

      }

      public function foodRating()
      {
        $res = Restaurant::getByFoodRating();

        if($res)
        {
          return json_encode([
            'status'  => 200,
            'method'  => 'restaurant_recommendation',
            'message' => 'sucess',
            'data'    => $res
           ]);
        }

        return json_encode([
          'status'  => 204,
          'method'  => 'restaurant_recommendation',
          'message' => 'unsucessful',
          'data'    => ""
         ]);
      }

      public function businessInfoRating($business_id)
      {
        $res = Restaurant::getInfoRating($business_id);
        if($res){
          return json_encode([
            'status'  => 200,
            'method'  => 'restaurant_rating_info',
            'message' => 'sucess',
            'data'    => $res
           ]);
        }
        return json_encode([
          'status'  => 204,
          'method'  => 'restaurant_rating_info',
          'message' => 'unsucessful',
          'data'    => ""
         ]);
      }
}
