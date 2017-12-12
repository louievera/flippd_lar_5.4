<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\User;

use App\Jobs\WelcomeEmail;

// use Mail;
// use Auth;
use App\Mail\SendValidation;

class UserController extends Controller
{
    function signUp(Request $request)
    {
      $header = apache_request_headers();
      $authKey = $header['Authorization'] ;

      if($authKey != "Bearer TaisonAPI-a0XbCZeTxi1zW9sU5Y2GoQf1M0G55m3JNPrHNH96JSJNpj2SOwaMUggW5V9U")
      {
        return json_encode([
          'status' => 204,
          'message' => 'invalid_authorization_key',
          'method' => 'authenticate_user',
          'data'	 => ""
        ]);
      }
      $validate = $this->signUpValidate($request->all());
      if($validate->fails())
      {

        return json_encode([
          'status' => 204,
          'message' => 'empty_result',
          'method' => 'authenticate_user',
          'data'	 => //$validate->errors()
          [
              'user_name' => $validate->errors()->first('user_name'),
              'email'     => $validate->errors()->first('email')
          ]
        ]);

      }
      $user = User::CreateUser($request);

      $email = request('email');
      $this->dispatch(new WelcomeEmail($email));


      return json_encode([
        'status' => 200,
        'message' => 'success',
        'method' => 'authenticate_user',
        'data'	 => $user
      ]);

    }

    function signUpValidate($request, $type=null)
    {
      $rules = [];

      $rules['user_name'] = 'required|unique:users';
    	$rules['first_name'] = 'required|min:2|max:50';
    	$rules['last_name'] = 'required|min:2|max:50';
      $rules['email'] = 'required|email|unique:users';

    	return Validator::make($request, $rules);
    }

    function changePass(Request $request, $id)
    {
      $validate = Validator::make($request->all(),["old_password" => "required",
                              "password" => "required|confirmed"
                            ]);

      if($validate->fails())
      {
        return json_encode([
          'status'    => 204,
          'message'   => 'empty_result',
          'method'    => 'change_password',
          'data'	    => [
                          'old_password' => $validate->errors()->first('old_password'),
                          'password'  => $validate->errors()->first('password') ]
            ]);
      }

      if(User::getPass($id,$request['old_password']) == 1){

        $passChange = User::updatePassword($id,$request);

        return json_encode([
          'status' => 200,
          'message' => 'password_updated',
          'method' => 'change_password',
          'data'	 => $passChange
        ]);
      }
      else {
        return json_encode([
          'status' => 204,
          'message' => 'empty_result',
          'method' => 'change_password',
          'data'	 => ['old_password' => 'password does not exist'] ]);
      }
    }

    function changeProfile(Request $request, $id)
    {
      $validate = Validator::make($request->all(),["first_name" => "required",
                              "last_name" => "required",
                              "email" => "email|required",
                              "photo" => "mimes:jpeg,jpg,png,svg"]
                            );

      $emailCnt = User::whereRaw('id <> '.$id)->where('email',$request['email'])->count();


      if($validate->fails() || $emailCnt > 0)
      {

        $emailVal = ($emailCnt > 0 ? "email already exist" : $validate->errors()->first('email'));
        return json_encode([
          'status'    => 204,
          'message'   => 'empty_result',
          'method'    => 'change_profile',
          'data'	    => [
                          'first_name'  => $validate->errors()->first('first_name'),
                          'last_name'   => $validate->errors()->first('last_name'),
                          'email'       => $emailVal,
                          'photo'       =>$validate->errors()->first('photo')
                         ]
            ]);
      }

          $res = User::updateProfile($id, $request);

          return json_encode([
            'status'  => 200,
            'message' => 'success',
            'method'  => 'change_profile',
            'data'	  => $res
          ]);
    }

    function contactUs(Request $request)
    {
      $validate = Validator::make($request->all(),
            [
                'name'    => 'required',
                'email'   => 'required|email',
                'message' => 'required'
            ]);
        if($validate->fails())
        {
            return json_encode([
              'status'    => 204,
              'message'   => 'empty_result',
              'method'    => 'contact_us',
              'data'	    => [
                              'name'    => $validate->errors()->first('name'),
                              'email'   => $validate->errors()->first('email'),
                              'message' => $validate->errors()->first('message'),
                             ]
                ]);
        }

        $res = User::insertContact($request);

        return json_encode([
          'status'  => 200,
          'message' => 'success',
          'method'  => 'contact_us',
          'data'	  => $res
        ]);
    }
}
