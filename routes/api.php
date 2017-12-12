<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth:api'], function (){

	Route::post('/authenticate/login', 'Api\Auth\AuthenticateController@login');

	Route::resource('business', 'Api\BusinessController', ['only' => [
	    'index', 'store'
	]]);

	Route::group(['prefix' => 'user'], function () {
	    Route::resource('business', 'Api\BusinessController', ['only' => [
		    'index', 'store'
		]]);

		Route::post('/password/{id}', 'Api\UserController@changePass');
		Route::post('/profile/{id}', 'Api\UserController@changeProfile');
		Route::post('/contact', 'Api\UserController@contactUs');
	});

	Route::group(['prefix' => 'business'], function () {
	    Route::resource('rating', 'Api\BusinessRatingController', ['only' => [
		    'index', 'store'
		]]);
		Route::post('reviewrating', 'Api\BusinessRatingController@reviewRating');

		Route::post('replycomment', 'Api\BusinessRatingController@replyComment');
	});

	Route::post('/signup', 'Api\UserController@signup');


	Route::group(['prefix'=>'restaurant'], function(){
		Route::get('/report/{business_id}/{startDate}/{endDate}', 'Api\RestaurantController@report');
		Route::get('/visit/{business_id}/{user_id}', 'Api\RestaurantController@visit');
		Route::post('/claim', 'Api\RestaurantController@claimRestaurant');
		Route::post('/login', 'Api\RestaurantController@login');
		Route::post('/description/{business_id}', 'Api\RestaurantController@description');
		Route::get('/getDescription/{id}', 'Api\RestaurantController@getDescription');
		Route::post('/thankyou/{business_id}', 'Api\RestaurantController@thankyouMessage');
		Route::post('/featuredcomment', 'Api\BusinessRatingController@featuredComment');
		Route::post('/photo', 'Api\RestaurantController@restaurantPhotos');
		Route::get('/foodrating', 'Api\RestaurantController@foodRating');
		Route::get('/businessInfoRating/{business_id}', 'Api\RestaurantController@businessInfoRating');
		Route::get('/comments/{business_id}', 'Api\BusinessRatingController@comments');
	});

});
