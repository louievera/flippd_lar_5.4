<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\User;

class AuthenticateController extends Controller
{

    public function login(Request $request)
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

      if(empty(request('facebook_id')))
      {
        return $this->nativeLogin($request);

      }

    	$validate = $this->authValidation($request->all());

    	if($validate->fails()){

          return json_encode([
    			'status' => 204,
          'message' => 'invalid_login',
    			'method' => 'authenticate_user',
    			'data'	 => $validate->errors()
    		]);

       }

    	$user = User::where('facebook_id', request('facebook_id'))->first();

    	if($user){
            if ($user->is_new) {
                $updated = User::where('id', $user->id)->update(['is_new' => 0]);
                if($updated){
                    $user->is_new = 0;
                }
            }

    		return json_encode([
    			'status' => 200,
          'message' => 'success',
    			'method' => 'authenticate_user',
    			'data'	 => $user
    		]);
    	}

    	$validate = $this->authValidation($request->all());

    	if($validate->fails()){

            return json_encode([
              'status' => 204,
              'message' => 'unsuccessful',
              'method' => 'authenticate_user',
              'data'	 => $validate->errors()
    		]);

       }

    	$createUser = User::createUser($request);

    	if($createUser){

    		return json_encode([
          'status' => 200,
          'message' => 'success',
    			'method' => 'authenticate_user',
    			'data'	 => $createUser
    		]);

    	}

    	return json_encode([
      'status' => 204,
      'message' => 'unsuccessful',
			'method' => 'authenticate_user',
			'data'	 => ''
		]);


    }

    public function authValidation($request, $type = null)
    {
    	$rules = [];
      // if(!empty(request('facebook_id')))
      // {
        $rules['facebook_name'] = 'required';
      	$rules['facebook_id'] = 'required';

      	if($type){
      		$rules['email'] = 'required|email|unique:users';

        }
        return  Validator::make($request, $rules);

    }

    public function nativeLogin(Request $request)
    {
        $checkUser = User::where('user_name',request('user_name'))->count();
        if($checkUser !== 0)
        {
          $rowUser = User::where('user_name',request('user_name'))->first();
          $pass = $rowUser->password;

          if ($rowUser->is_new) {
              $updated = User::where('id', $rowUser->id)->update(['is_new' => 0]);
              if($updated){
                  $rowUser->is_new = 0;
              }
          }

          if(password_verify(request('password'), $pass )) {
            $res = json_encode([
              'status' => 200,
              'message' => 'success',
              'method' => 'authenticate_user',
              'data' =>   $rowUser
              ]);
          }
          else {
            $res = json_encode([
              'status' => 204,
              'message' => 'empty_result',
              'method' => 'authenticate_user',
              'data' =>   array('password' => "Password did not match")
            ]);
          }
        }
        else {
          $res = json_encode([
            'status' => 204,
            'message' => 'empty_result',
            'method' => 'authenticate_user',
            'data' =>   array('user_name' => "User doesn't exist")
          ]);
        }

        return $res;
    }


}
