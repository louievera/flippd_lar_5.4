<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB, App\BusinessRating;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Restaurant extends Model
{
    public static function getReports($payload)
    {
      $query = BusinessRating::selectRaw('
                                    COUNT(*) as total_count,
                                    COUNT(CASE WHEN food_quality = 1 THEN 1 END) AS yummy_count,
                                    COUNT(CASE WHEN food_quality = 2 THEN 1 END) AS yuck_count,
                                    COUNT(CASE WHEN food_quality = 3 THEN 1 END) AS alright_count,
                                    COUNT(CASE WHEN service_total BETWEEN "-1" AND "6" THEN 1 END) AS good_count,
                                    COUNT(CASE WHEN service_total BETWEEN "-6" AND "-2" THEN 1 END) AS bad_count,
                                    (SELECT COUNT(*) FROM restaurant_visit WHERE business_id = "'.$payload["business_id"].'"
                                      AND DATE(created_at) BETWEEN "'.$payload['date_start'].'" AND "'.$payload['date_end'].'") as visit
                                        ')
                             ->where('business_id', $payload['business_id'])
                             ->whereRaw('DATE(created_at) BETWEEN "'.$payload['date_start'].'" AND "'.$payload['date_end'].'"')
                             ->orderBy('created_at', 'DESC')
                             ->first();

      return [
          'total_rating'   => $query->total_count,
          'visit_count' => $query->visit,
          'service_rating' => [
            'total_good' => $query->good_count,
            'total_bad'  => $query->bad_count,
          ],
          'food_quality' => [
            'total_yummy'   => $query->yummy_count,
            'total_yuck'    => $query->yuck_count,
            'total_alright' => $query->alright_count,
          ]
      ];
    }

    public static function insertVisit($ids)
    {
      $business = $ids['business'];
      $user     = $ids['user'];

      $res = [
              "business_id" => $business,
              "user_id"     => $user
            ];

      $timeNow = date('Y-m-d H:i:s');

      $time = DB::table('restaurant_visit')
              ->select('created_at')
              ->where('business_id', $business)
              ->where('user_id',$user)
              ->orderBy('created_at','DESC')
              ->limit(1)
              ->first();

        if(empty($time))
        {
          DB::table('restaurant_visit')->insert($res);
          return $res;
        }
        else
        {
          $dif = strtotime($timeNow) - strtotime($time->created_at);
          if($dif > 20)
          {
            DB::table('restaurant_visit')->insert($res);
            return $res;
          }
        }
        return "";
    }

    public static function insertClaimRestaurant($request)
    {
      $document = $request->file('document');

      $data = [
                 "restaurant_name"  => $request['business_name'],
                 "owner_name"       => $request['owner_name'],
                 "email"            => $request['email'],
                 "mobile_number"    => $request['mobile_number'],
                 "document"         => $document->hashName()
               ];

      DB::table('business_request')->insert($data);

    }

    public static function getRestaurantOwner($request)
    {

      $path = asset('storage/restaurant_photos/');
      $res = DB::table('business_owner as bo')
                    ->selectRaw('br.id as ids, b.business_id, b.name, b.description,
                                b.thankyou_message, bo.user_name, bo.password,bo.name as owner_name,
                                bo.email, bo.is_new, bo.id as owner_id,
                                GROUP_CONCAT(DISTINCT br.comment SEPARATOR "--" ) as comment,
                                GROUP_CONCAT(DISTINCT "'.$path.'/",bp.image SEPARATOR "--") as image')
                    ->leftJoin('business as b', 'b.business_id', 'bo.business_id')
                    ->leftJoin('business_rating as br','bo.business_id','br.business_id')
                    ->leftJoin('business_photo as bp', 'bo.business_id','bp.business_id')
                    ->where('br.featured_comment',1)
                    ->where('user_name',$request['username'])
                    ->groupBy('b.business_id')->get();

      // dd($res);
      return $res;
    }

    public static function insertDescription($request, $business_id)
    {
      $data = ["description" => $request['description']];
      DB::table('business')->where("business_id",$business_id)->update($data);

      return $data;
    }

    public static function getDescriptionInfo($id)
    {
      $res = DB::table('business')->selectRaw('description, thankyou_message')->where("id", $id)->first();
      return $res;
    }

    public static function insertThankyoumessage($request, $business_id)
    {
      $data = ["thankyou_message" => $request['thankyou_message']];
      DB::table('business')->where("business_id",$business_id)->update($data);

      return $data;
    }

    /* for featured photo */
    public static function setFeaturedPhoto($request)
    {
      $table = DB::table("business_photo");

      $photo = $request->file('image');
      if(isset($photo))
      {
        $data = ["business_id"=>$request['business_id'],
                  "image" => $photo->hashName()];

        // $photo->store('restaurant_photos');
        $resize = Image::make($photo->getRealPath());
        $resize->resize(1440,900);
        $resize->save(storage_path('app/public/restaurant_photos/'.$photo->hashName()));

        $table->insert($data);
      }
      else {
        $data = ["business_id"=>$request['business_id'] ];

        $table->insert($data);
      }
      return $data;
    }

    public static function getByFoodRating()
    {
      $res = DB::table('business as b')
              ->select('b.name, b.business_id,br.food_quality,br.service_total')
              ->leftJoin('business_rating as br','b.business_id','br.business_id')
              ->groupBy('b.business_id')
              ->orderBy('br.food_quality','desc')
              ->get();

      return $res;
    }

    public static function getInfoRating($business_id)
    {
      $path = asset('storage/restaurant_photos/');
      $businessInfo = DB::table('business as b')
                  ->selectRaw('b.name, b.business_id, b.description, b.thankyou_message,
                        GROUP_CONCAT(DISTINCT br.comment SEPARATOR "--") as comments,
                        GROUP_CONCAT(DISTINCT"'.$path.'/",bp.image SEPARATOR "--") as images')
                  ->leftJoin('business_rating as br', 'b.business_id','br.business_id')
                  ->leftJoin('business_photo as bp', 'b.business_id','bp.business_id')
                  ->where('b.business_id',$business_id)
                  ->groupBy('b.business_id')
                  ->get();

      return [
        'business_id' => $businessInfo[0]->business_id,
        'name' => $businessInfo[0]->name,
        'description' => $businessInfo[0]->description,
        'thankyou_message' => $businessInfo[0]->thankyou_message,
        'comments' => explode('--', $businessInfo[0]->comments),
        'images' => explode('--', $businessInfo[0]->images),

      ];
    }
}
