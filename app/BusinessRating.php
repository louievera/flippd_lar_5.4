<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;



class BusinessRating extends Model
{
	protected $table = 'business_rating';
	protected $fillable = ['business_id', 'service_total', 'food_quality', 'comment', 'user_id'];

	public function scopeStore($query, $data) {
		if(isset($data)) {
			return BusinessRating::create($data)->id;
		}

		return false;
	}

    public function scopeGetBusinessRating($query, $business_id) {

		if(isset($business_id)) {
		    return BusinessRating::select('r.id', 'r.comment', 'r.user_id', 'u.facebook_name', 'u.photo')
		    					->from('business_rating as r')
    							->leftJoin('users as u', 'u.id', 'r.user_id')
    							->where('business_id', $business_id)
    							->get();
		}
		return false;
    }

    public function scopeGetAverageRatingByIds($query, $business_ids) {
    	if(isset($business_ids)) {
    		$ratings = BusinessRating::select('service_total', 'food_quality', 'business_id')
    									->whereIn('business_id', $business_ids)
    									->get();

    		return $ratings->groupBy('business_id')->map(function($item, $key)
				{
					$rateComment = BusinessRating::select('r.comment', 'r.food_quality', 'r.service_total', 'u.photo','u.first_name','u.last_name','u.facebook_name')
																		->from('business_rating as r')
																		->leftJoin('users as u', 'u.id', 'r.user_id')
																		->where('business_id',$item[0]->business_id)
																		->get();

					$data['business_id'] = $item[0]->business_id;
    			$data['average_food_quality'] = round($item->avg('food_quality'));
    			$data['average_service_total'] = round($item->avg('service_total'));
					$data['review']= $rateComment;
					// $data['food_quality'] = $item[0]->food_quality;

					return $data;
    		})->values();

    	}
		return false;
    }

		public function scopeGetReviewRating($query, $id)
		{
			$res = DB::table('business_rating')
						// ->select('comment','food_quality')
						->where('business_id',$id)->get();

			return $res;
		}

		public static function insertNotification($request)
		{
			$get = DB::table('user_notification as un')
										->select('br.id', 'br.business_id', 'u.api_token','br.user_id', 'br.comment', 'un.message', 'un.read')
										->leftJoin('business_rating AS br', 'br.id','un.rating_id')
										->leftJoin('users AS u','br.user_id','u.id')
										->where('un.rating_id',$request['comment_id']);
			if($get->count() == 0)
			{
				 $data = ['rating_id' => $request['comment_id'], 'message' => $request['message']];
				 DB::table('user_notification')->insert($data);

				 return $get->get();
			}

			return null;
			// return $get->toSql();
		}

		public static function getComments($id)
		{
			$path = asset('storage/user_avatar/');
			$res = DB::table('business_rating as br')
									->selectRaw("concat('".$path."/',u.photo) as photo, u.user_name, u.facebook_name, u.first_name, br.comment, br.service_total, br.food_quality")
									->leftJoin('users as u', 'u.id','br.user_id')
									->where('br.business_id',$id)
									->orderBy('featured_comment','desc')->get();
			return $res;
		}


	    public static function setFeaturedComment($request, $ids)
	    {
				// $res = 'test';

	        $business_id = $request->business_id;
	        $table = DB::table("business_rating");
	        $whereBusiness = $table->where("business_id",$business_id);

	        $whereBusiness->update(['featured_comment' => 0]);

	        $whereBusiness->whereIn("id",$ids)
	                     ->update(['featured_comment' => 1]);
          //
	        $res = $whereBusiness->where("featured_comment",1)->get();
	        return $res;
	    }

}
