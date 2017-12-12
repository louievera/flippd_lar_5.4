<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Business;
use App\BusinessRating;

class BusinessController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // dd(request()->user());
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business = User::find(request()->user()->id)->business;

        if($business){

            return json_encode([
                'status' => 200,
                'message' => 'success',
                'method' => 'business_collection',
                'data'   => $business->toArray()
            ]);
        }

        return json_encode([
            'status' => 204,
            'message' => 'empty_result',
            'method' => 'business_collection',
            'data'   => ''
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $data = json_decode(request('data'));

        if(isset($data->id)){

            $result = Business::Store($data);

            return json_encode([
                'status' => 200,
                'message' => 'success',
                'method' => 'business_store',
                'data' => $result
            ]);
        }

        if(isset($data->business_ids)){

            $result = BusinessRating::GetAverageRatingByIds($data->business_ids);

            return json_encode([
                'status' => 200,
                'message' => 'success',
                'method' => 'business_ratings',
                'data' => $result
            ]);
        }

        return json_encode([
            'status' => 204,
            'message' => 'empty_result',
            'method' => 'business_store',
            'data'   => ''
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
