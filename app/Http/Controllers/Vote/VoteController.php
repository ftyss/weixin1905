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

        $this->getAccessToken($code);   //获取access_token
    }

    /**
     * 根据code获取access_token
     */
    protected function getAccessToken($code)
    {
        $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET').'&code='.$code.'&grant_type=authorization_code';
        $json_data=file_get_contents($url);
        $data=json_decode($json_data,true);
        echo '<pre>';print_r($data);echo '</pre>';
    }
}
