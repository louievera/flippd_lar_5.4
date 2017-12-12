<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use DB;

class admin_user extends Authenticatable
{
    //
    protected $table = 'admin_users';
}
