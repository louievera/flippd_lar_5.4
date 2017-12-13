<?php

// use Mail/SendValidation;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix'=>'cms'], function(){
  Route::get('list/', 'restaurantCmsController@viewList');
  Route::get('view/{id}/', 'restaurantCmsController@view');
  Route::get('verify/{id}', 'restaurantCmsController@verifyRestorant');
  Route::get('verifyOwner/{id}/{token}/{business_id}','restaurantCmsController@insertUser');
});
