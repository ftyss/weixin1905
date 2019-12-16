<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WxUserModel extends Model
{
    //微信关注用户表
    protected $table = 'p_wx_users';
    protected $primaryKey='uid';
}
