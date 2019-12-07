<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\UserModel;

use GuzzleHttp\Client;

class LoginController extends Controller
{
    public function addUser()
    {
        $pass = '12345';
        $email = 'fangtao@qq.com';
        //使用密码函数
        $password  = password_hash($pass,PASSWORD_BCRYPT);
 
        $data = [
            'user_name' => 'fangtao',
            'password'  => $password,
            'email'     => $email,
        ];

        $uid = UserModel::insertGetId($data);
        var_dump($uid);
    }

    
}
