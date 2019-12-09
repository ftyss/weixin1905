<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WxController extends Controller
{
    protected $access_token;

    public function __construtc()
    {
        //获取access_token
        $this->access_token=$this->getAccessToken();
    }

    protected function getAccessToken()
    {
        $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env(WX_APPID).'&secret='.env(WX_APPSECRET);
        $data_json=file_get_contents($url);
        $arr=json_decode($data-json,true);
        return $arr['access_token'];
    }

    /**
     * 处理接入
     */
    public function weixin()
    {
        $token = 'nsdjsjsajvndsjk';
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $echostr = $_GET['echostr'];

        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
    
        if( $tmpStr == $signature ){
            echo $echostr;
        }else{
            die("not ok");
        }
       
    }

    /**
     * 接收微信推送事件
     */
    public function receiv()
    {
        $log_file="wx.log";         //默认写在public目录下
        //将接收到的数据添加到日志中
        $xml_str=file_get_contents("php://input");
        $data=date('Y-m-d H:i:s') . $xml;
        file_put_contents($log_file,$data,FILE_APPEND);     //FILE_APPEND追加写

        //处理xml数据
        $xml_obj=simplexml_load_string($xml_str);

        $event=$xml_obj->Event;         //获取事件类型
        if($event=='subscribe'){
            $openid=$xml_obj->FromUserName;     //获取用户的openid
        }

        //获取用户基本信息
        $url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->access_token.'&openid='.$openid.'&lang=zh_CN';
        $user_info=file_get_contents('wx_user.log',$user_info,FILE_APPEND);
    }


    /**
     * 获取用户基本信息
     */
    public function getUserInfo($access_tolen,$openid)
    {
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN";
        //发送网络请求
        $json_obj=file_get_contents($url);
        $log_file="wx_user.log";
        file_put_contents($log_file,$json_str,FILE_APPEND);
    }
}
