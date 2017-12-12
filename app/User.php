<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'api_token', ' is_new', 'last_name', 'first_name', 'last_name',
        'email_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'age', 'birthdate', 'contact_number'
    ];

    public function business()
    {
        return $this->hasMany('App\Business');
    }

    public function scopeCreateUser($query, $request)
    {
        $user = new User();

        if(!empty($request->get('facebook_id'))) {
          $user->facebook_id = $request->get('facebook_id');
          $user->facebook_name        = $request->get('facebook_name');
          $user->email       = $request->get('email');
          $user->photo       = $request->get('photo');
          $user->password    = bcrypt('taisonFlippd');
          $user->api_token   = str_random(60);
        }

        else {
          $user->user_name   = $request->get('user_name');
          $user->first_name  = $request->get('first_name');
          $user->last_name   = $request->get('last_name');
          $user->email       = $request->get('email');
          $user->api_token   = str_random(60);
          $user->password    = bcrypt($request->get('password'));
          $user->contact_number = $request->get('contact_number');

        }
        if($user->save()){

            $res = User::where('user_name',$request->get('user_name'))->first();
            return $res;
        }

        return false;

    }

    public static function updatePassword($id, $request)
    {
        User::where('id',$id)
            ->update(['password' => bcrypt($request['password'])]);

            return array('id' => $id, 'password' => bcrypt($request['password']));
    }

    public static function getPass($id, $requestPass)
    {
      $oldPass = User::find($id);

      if(password_verify($requestPass, $oldPass['password']))
      {
        return 1;
      }
      else {
        return 0;
      }
    }

    public static function updateProfile($id, $request)
    {
      $userId = User::find($id);
      $photo = $request->file('photo');
      if(isset($photo))
      {
        $data = ['first_name'   =>  $request['first_name'],
                  'last_name'   =>  $request['last_name'],
                  'email'       =>  $request['email'],
                  'photo'       =>  $photo->hashName()
                ];
        $photo->store('public/user_avatar');
      }
      else {
        $data = ['first_name'   =>  $request['first_name'],
                  'last_name'   =>  $request['last_name'],
                  'email'       =>  $request['email']
                ];
      }

      $update = User::where('id',$id)->update($data);
      return $data;
    }


    public static function insertContact($request)
    {
      $data = [
                'name' => $request['name'],
                'email' => $request['email'],
                'mobile_number' => $request['mobile_number'],
                'message' => $request['message']
              ];
      DB::table('contact')->insert($data);
      return $data;
    }
}
