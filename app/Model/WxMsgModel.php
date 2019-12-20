<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WxMsgModel extends Model
{
    ///微信用户留言表
    protected $table = 'p_wx_msg';
    protected $primaryKey='uid';
}
