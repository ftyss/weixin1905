<?php

namespace App\Http\Controllers\Vote;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class VoteController extends Controller
{
    public function index()
    {
        //echo '<pre>';print_r($_GET);echo '</pre>';
        $code=$_GET['code'];

        $data = $this->getAccessToken($code);   //获取access_token

        //获取用户信息
        $user_info=$this->getUserInfo($data['access_token'],$data['openid']);
        //处理业务逻辑
        
        $openid=$user_info['openid'];
        $key='ss:vote:fangtao';

        //判断用户是否已经投过票
        if(Redis::zrank($key,$user_info['openid'])){
            echo "你已经投过票了，多次投票无效";
        }else{
            Redis::Zadd($key,time(),$openid);
        }
        
        $members=Redis::zRange($key,0,-1,true);         //获取所有投票用户的openid
        echo '<pre>';print_r($members);echo '</pre>';
        foreach($members as $k=>$v){
            echo "用户：".$k .'投票时间：'. date('Y-m-d H:i:s',$v);echo '</br>';
        }
        // $total=Redis::Scard($key);              //获取投票总人数
        // echo "   投票总人数： ".$total;
        // echo '<hr>';
        // echo '<pre>';print_r($members);echo '</pre>';
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
        return $data;       //返回用户信息

    }    
}
