<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BusinessRating;
use DB;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use LaravelFCM\Message\Topics;

class BusinessRatingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ratings = BusinessRating::find(request()->user()->id)->ratings;

        if(isset($ratings)){

            return json_encode([
                'status' => 200,
                'method' => 'business_collection',
                'message' => 'success',
                'data'   => $ratings->toArray()
            ]);
        }

        return json_encode([
            'status' => false,
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
         $data = json_decode(request('rating'));
         if(isset($data)){
             $result = BusinessRating::Store((array)$data);
             return json_encode([
                 'status' => 200,
                 'method' => 'rating_store',
                 'message' => 'success',
                 'data' => $result,
                  'id' => $data->business_id
             ]);

         }

         else {
           return $this->reviewBusinessId($request->business_id);
         }
         if(isset($data->business_id)){
             $result = BusinessRating::GetBusinessRating($data->business_id);
             return json_encode([
                 'status' => 200,
                 'method' => 'rating_show',
                 'message' => 'success',
                 'data' => $result

             ]);
         }
         return json_encode([
             'status' => 204,
             'message' => 'empty_result',
             'method' => 'rating_store',
             'data'   => ''
         ]);
     }

    public function reviewRating(Request $request)
    {
        $data = json_decode(request('rating'));

        if(isset($data)){

            $result = BusinessRating::Store((array)$data);

            return json_encode([
                'status' => 200,
                'method' => 'rating_store',
                'message' => 'success',
                'data' => $result
            ]);
        }

        if(isset($data->business_id)){

            $result = BusinessRating::GetBusinessRating($data->business_id);

            return json_encode([
                'status' => 200,
                'method' => 'rating_show',
                'message' => 'success',
                'data' => $result
            ]);
        }

        return json_encode([
            'status' => 204,
            'message' => 'empty_result',
            'method' => 'rating_store',
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

    public function reviewBusinessId($id)
    {
      $row = BusinessRating::GetReviewRating($id);
      if(count($row)!=0)
      {
        return json_encode([
          'status' => 200,
          'method' => 'rating_show',
          'message' => 'success',
          "total_review_count" => count($row),
          "data" => $row
        ]);
      }
      else {
        return json_encode([
          'status' => 204,
          'message' => 'empty_result',
          'method' => 'rating_show',
          'data' => ''
          ]);
      }
    }

    public function replyComment(Request $request)
    {
      $res = BusinessRating::insertNotification($request);
      switch($res)
      {
        case null:
            $res = json_encode([
              'status' => 204,
              'message' => 'empty_result',
              'method' => 'reply_comment',
              'data' => ''
              ]);
         break;

         default:
           $res = json_encode([
             'status' => 200,
             'method' => 'rating_show',
             'message' => 'success',
             "data" => $res ]);

          // $this->downStreamMsg($request->token, $res);
        }
        return $res;
    }

    public function downStreamMsg($userToken, $body)
		{
      $optionBuilder = new OptionsBuilder();
      $optionBuilder->setTimeToLive(60*20);

      $notificationBuilder = new PayloadNotificationBuilder('Restaurant reply to your comment');
      $notificationBuilder->setBody($body)->setSound('default');

      $dataBuilder = new PayloadDataBuilder();
      $dataBuilder->addData(['a_data' => 'my_data']);

      $option = $optionBuilder->build();
      $notification = $notificationBuilder->build();
      $data = $dataBuilder->build();

      $token = $userToken;

      $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

      $downstreamResponse->numberSuccess();
      $downstreamResponse->numberFailure();
      $downstreamResponse->numberModification();

      //return Array - you must remove all this tokens in your database
      $downstreamResponse->tokensToDelete();

      //return Array (key : oldToken, value : new token - you must change the token in your database )
      $downstreamResponse->tokensToModify();

      //return Array - you should try to resend the message to the tokens in the array
      $downstreamResponse->tokensToRetry();

		}

    public function comments($id)
    {
      $data = BusinessRating::getComments($id);

      return $res = json_encode([
        'status' => 200,
        'method' => 'rating_show',
        'message' => 'success',
        "data" => $data ]);

   }

   public function featuredComment(Request $request)
   {
     $ids = explode(",",$request->id);
     $res = BusinessRating::setFeaturedComment($request,$ids);

     if($res->count() != 0)
     {
       return json_encode([
         'status' => 200,
         'method' => 'featured_comments',
         'message' => 'success',
         "data" => $res ]);
     }

     return json_encode([
       'status' => 204,
       'method' => 'featured_comments',
       'message' => 'unsuccessful',
       "data" => "" ]);

   }
}
