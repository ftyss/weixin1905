<?php

namespace App\Http\Controllers\Vote;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class VoteController extends Controller
{
    public function index()
    {
        echo '<pre>';print_r($_GET);echo '</pre>';
        $code=$_GET['code'];

        $data = $this->getAccessToken($code);   //获取access_token

        //获取用户信息
        $user_info=$this->getUserInfo($data['access_token'],$data['openid']);
        //处理业务逻辑
        $redis_key='vote';
        $number=Redis::incr($redis_key);
        echo "投票成功，已得票数".$number;
    }

    /**
     * 根据code获取access_token
     */
    protected function getAccessToken($code)
    {
        $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET').'&code='.$code.'&grant_type=authorization_code';
        $json_data=file_get_contents($url);
        return json_decode($json_data,true);
    }

    /**
     * 获取用户基本信息
     */
    protected function getUserInfo($access_token,$openid)
    {
        $url='https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $json_data=file_get_contents($url);
        $data=json_decode($json_data,true);
        //echo '<pre>';print_r($usr_info);echo '</pre>';
        if(isset($data['errcode'])){
            die("出现错误");        //报错信息
        }
        return $usr_info;       //返回用户信息

    }    
}
