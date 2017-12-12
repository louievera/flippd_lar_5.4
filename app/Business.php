<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\BusinessRating;
use App\BusinessPhoto;

class Business extends Model
{
    protected $table = 'business';
    protected $fillable = ['business_id'];

    public function scopeStore($query, $data) {
		if(isset($data)) {
		    $business = Business::firstOrNew([ 'business_id' => $data->id ]);
		    $business->name = $data->name;
		   	$business->address = $data->vicinity;
		   	$business->type = implode(',', $data->types);
		   	$business->average_price = isset($data->price) ? $data->price : 0;
		   	$business->about = isset($data->description) ? $data->description : '';

	   		if($business->save()) {
	   			$rating = BusinessRating::getBusinessRating($business->business_id);
	   			$business->ratings = $rating->slice(0,3);
	   			$business->average_service_total = $rating->avg('service_total') ? $rating->avg('service_total') : 0;
	   			$business->average_food_quality = $rating->avg('food_quality') ? $rating->avg('food_quality') : 0;
	   		}
	   		return $business;
		}
    	return false;
    }

}
