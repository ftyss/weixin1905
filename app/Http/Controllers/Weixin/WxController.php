<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WxUserModel;

class WxController extends Controller
{
    protected $access_token;

    public function __construct()
    {
        //获取access_token
        $this->access_token=$this->getAccessToken();
    }

    protected function getAccessToken()
    {
        $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET');
        $data_json=file_get_contents($url);
        $arr=json_decode($data_json,true);
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
        $data=date('Y-m-d H:i:s') . $xml_str;
        file_put_contents($log_file,$data,FILE_APPEND);     //FILE_APPEND追加写
        $xml_arr=simplexml_load_string($xml_str);

        //处理xml数据
        $xml_obj=simplexml_load_string($xml_str);

        $event=$xml_obj->Event;         //获取事件类型
        if($event=='subscribe'){
            $openid=$xml_obj->FromUserName;     //获取用户的openid
            $name=$xml_obj->ToUserName;         //开发者公众号id
            $time=time();
            //判断用户曾经是否关注过
            $u=WxUserModel::where(['openid'=>$openid])->first();
            if($u){
                //曾经关注
                $guanzhuhuifu='<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$name.']]></FromUserName>
                    <CreateTime>'.$time.'</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[欢迎回家]]></Content>
                </xml>';
                echo $guanzhuhuifu;die;

            }else{
                $user_data=[
                    'openid'=>$openid,
                    'sub_time'=>$xml_obj->CreateTime,
                ];
    
                //openid 存入数据库
                $uid=WxUserModel::insertGetId($user_data);
                var_dump($uid);
                $guanzhuhuifus='<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$name.']]></FromUserName>
                    <CreateTime>'.$time.'</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[欢迎关注]]></Content>
                </xml>';
                echo $guanzhuhuifus;
            }
            

            //获取用户基本信息
        $url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->access_token.'&openid='.$openid.'&lang=zh_CN';
        $user_info=file_get_contents($url);
        file_put_contents('wx_user.log',$user_info,FILE_APPEND);
        }

        //回复消息
        $msg_type=$xml_obj->MsgType;

        $touser=$xml_obj->FromUserName;     //获取用户openid
        $formuser=$xml_obj->ToUserName;     //开发者公众号的id
        $time=time();

        if($msg_type=='text'){
            $content=date('Y-m-d H:i:s').$xml_obj->Content;
            $response_text='<xml><ToUserName><![CDATA['.$touser.']]></ToUserName>
                        <FromUserName><![CDATA['.$formuser.']]></FromUserName>
                        <CreateTime>'.$time.'</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA['.$content.']]></Content>
                        </xml>';

            echo $response_text;
        }

        
    }


    /**
     * 获取用户基本信息
     */
    public function getUserInfo($access_token,$openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        // 发送网络请求
        $json_str = file_get_contents($url);
        $log_file = 'wx_user.log';
        file_put_contents($log_file,$json_str,FILE_APPEND);
    }
}
