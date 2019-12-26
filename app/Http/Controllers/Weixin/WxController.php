<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WxUserModel;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use App\Model\WxMsgModel;
use Illuminate\Support\Str;


class WxController extends Controller
{
    protected $access_token;

    public function __construct()
    {
        //获取access_token
        $this->access_token=$this->getAccessToken();
    }

    public function test()
    {
        echo $this->access_token;
    }

    protected function getAccessToken()
    {
        $key='wx_access_token';
        $access_token=Redis::get($key);
        if($access_token){
            return $access_token;
        }

        $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_APPSECRET');
        $data_json=file_get_contents($url);
        $arr=json_decode($data_json,true);

        Redis::set($key,$arr['access_token']);
        Redis::expire($key,3600);
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
        $data=date('Y-m-d H:i:s') .">>>>>>>\n". $xml_str."\n\n";
        file_put_contents($log_file,$data,FILE_APPEND);     //FILE_APPEND追加写
        //$xml_arr=simplexml_load_string($xml_str);

        //处理xml数据
        $xml_obj=simplexml_load_string($xml_str);

        $event=$xml_obj->Event;         //获取事件类型
        $openid=$xml_obj->FromUserName;     //获取用户的openid
        if($event=='subscribe'){
            
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
                    <Content><![CDATA[欢迎回来]]></Content>
                </xml>';
                echo $guanzhuhuifu;die;

            }elseif($event=='subscribe'){
                //获取用户基本信息
                $url='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->access_token.'&openid='.$openid.'&lang=zh_CN';
                $user_info=file_get_contents($url);
                $u=json_decode($user_info,true);
                $nickname=$u['nickname'];
              
                $user_data=[
                    'openid'=>$openid,
                    'nickname'=>$u['nickname'],
                    'sex'=>$u['sex'],
                    'headimgurl'=> $u['headimgurl'],
                    'subscribe_time' => $u['subscribe_time']
                ];
    
                //openid 存入数据库
                $uid=WxUserModel::insertGetId($user_data);
                //$rmj="感谢".$nickname."你的关注";
                $rmj="欢迎".$nickname."同学进入选课系统";
                $guanzhuhuifus='<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$name.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA['.$rmj.']]></Content>
                </xml>';
                echo $guanzhuhuifus;
            }
        }elseif($event=='CLICK'){           //点击事件
            
            //echo "123";die;

            if($xml_obj->EventKey=='weather'){
                $weather_api='https://free-api.heweather.net/s6/weather/now?location=beijing&key=0eced2803331478083c559f863fe4924';
                $weather_info=file_get_contents($weather_api);
                $weather_info_arr=json_decode($weather_info,true);
                //echo '<pre>';print_r($weather_info_arr);echo '</pre>';die;
                $cond_txt=$weather_info_arr['HeWeather6'][0]['now']['cond_txt'];
                $tmp=$weather_info_arr['HeWeather6'][0]['now']['tmp'];
                $wind_dir=$weather_info_arr['HeWeather6'][0]['now']['wind_dir'];

                $msg=$cond_txt .'温度： '.$tmp . '风向： '. $wind_dir;

                $response_xml='<xml><ToUserName><![CDATA['.$openid.']]></ToUserName>
                <FromUserName><![CDATA['.$xml_obj->ToUserName.']]></FromUserName>
                <CreateTime>'.time().'</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA['.date('Y-m-d H:i:s').$msg.']]></Content>
                </xml>';
                echo $response_xml;
            }
        }
            

            

        //回复消息
        $msg_type=$xml_obj->MsgType;

        $touser=$xml_obj->FromUserName;     //获取用户openid
        $fromuser=$xml_obj->ToUserName;     //开发者公众号的id
        $time=time();
        $title=$xml_obj->Title;
        $description=$xml_obj->Description;

        $media_id=$xml_obj->MediaId;

        if($msg_type=='text'){
            $content=date('Y-m-d H:i:s').$xml_obj->Content;
            $openid = $xml_obj->FromUserName; 
            $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->access_token.'&openid='.$openid.'&lang=zh_CN';
            $msg_info = file_get_contents($url);       
            $m = json_decode($msg_info,true);
            $Msg_data = [
               'openid' => $openid,
               'nickname' => $m['nickname'],
               'content' => $xml_obj->Content,
           ]; 
           //openid 入库
           $uid = WxMsgModel::insertGetId($Msg_data);


            $response_text='<xml><ToUserName><![CDATA['.$touser.']]></ToUserName>
                        <FromUserName><![CDATA['.$fromuser.']]></FromUserName>
                        <CreateTime>'.$time.'</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA['.$content.']]></Content>
                        </xml>';

            echo $response_text;        //回复用户文本消息
        }elseif($msg_type=='image'){
            //下载图片
            $this->getMedia2($media_id,$msg_type);
            //回复图片
            $response='<xml>
            <ToUserName><![CDATA['.$touser.']]></ToUserName>
            <FromUserName><![CDATA['.$fromuser.']]></FromUserName>
            <CreateTime>'.time().'</CreateTime>
            <MsgType><![CDATA[image]]></MsgType>
            <Image>
              <MediaId><![CDATA['.$media_id.']]></MediaId>
            </Image>
          </xml>';

            echo $response;

        }elseif($msg_type=='voice'){
            //下载语音
            $this->getMedia2($media_id,$msg_type);
            //回复语音
            $response='<xml>
            <ToUserName><![CDATA['.$touser.']]></ToUserName>
            <FromUserName><![CDATA['.$fromuser.']]></FromUserName>
            <CreateTime>'.time().'</CreateTime>
            <MsgType><![CDATA[voice]]></MsgType>
            <Voice>
              <MediaId><![CDATA['.$media_id.']]></MediaId>
            </Voice>
          </xml>';

            echo $response;

        }elseif($msg_type=='video'){
            //下载视频
            $this->getMedia2($media_id,$msg_type);
            //回复视频
            $response='<xml>
            <ToUserName><![CDATA['.$touser.']]></ToUserName>
            <FromUserName><![CDATA['.$fromuser.']]></FromUserName>
            <CreateTime>'.time().'</CreateTime>
            <MsgType><![CDATA[video]]></MsgType>
            <Video>
              <MediaId><![CDATA['.$media_id.']]></MediaId>
              <Title><![CDATA['.$title.']]></Title>
              <Description><![CDATA['.$description.']]></Description>
            </Video>
          </xml>';

            echo $response;

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

    /**
     * 获取素材
     */
    public function getMedia()
    {
        $media_id='Hwk-HRSS-OQdvfxkt4wjqkZwxiFkC3-fVQga5o60F4RaDIL5_lvWnc3JjULDrh2y';
        $url='https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token.'&media_id='.$media_id;
        
        //获取素材内容
        $data = file_get_contents($url);
        // 保存文件
        $file_name = date('YmdHis').mt_rand(11111,99999) . '.amr';
        file_put_contents($file_name,$data);
        echo "下载素材成功";echo '</br>';
        echo "文件名： ". $file_name;
    }

    protected function getMedia2($media_id,$media_type)
    {
        $url='https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->access_token.'&media_id='.$media_id;
        //获取素材内容
        $client=new Client();
        $response=$client->request('GET',$url);
        //dd($response);
        //获取文件扩展名
        $f=$response->getHeader('Content-disposition')[0];
        $extension = substr(trim($f,'"'),strpos($f,'.'));
        //获取文件内容
        $file_content = $response->getBody();

        //保存文件
        $save_path = 'wx_media/';
        if($media_type=='image'){       //保存图片文件
            $file_name = date('YmdHis').mt_rand(11111,99999) . $extension;
            $save_path = $save_path . 'imgs/' . $file_name;
        }elseif($media_type=='voice'){  //保存语音文件
            $file_name = date('YmdHis').mt_rand(11111,99999) . $extension;
            $save_path = $save_path . 'voice/' . $file_name;
        }elseif($media_type=='video'){   //保存视频文件
            $file_name = date('YmdHis').mt_rand(11111,99999) . $extension;
            $save_path = $save_path . 'video/' . $file_name;
        }

        file_put_contents($save_path,$file_content);
    }


    /**
     * 刷新access_token
     */
    public function flushAccessToken()
    {
        $key = 'wx_access_token';
        Redis::del($key);
        echo $this->getAccessToken();
    }

    /**
     * 自定义菜单
     */
    public function createMenu()
    {
        $url='http://1905.fangtaoys.com/vote';
        $redirect_uri=urlencode($url);  //授权后跳转页面
        $url2='http://1905.fangtaoys.com';
        $redirect_uri2=urlencode($url2);  


        //调用自定义菜单接口
        $url ='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->access_token;    //调用接口
        $menu=[
            'button'=>[
                [
                    'type' => 'click',
                    'name' => '获取天气',
                    'key' => 'weather'
                ],
                [
                    'name'=>'菜单',
                    'sub_button'=>[
                        [
                            'type' => 'view',
                            'name' => '投票',
                            'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.env('WX_APPID').'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state=asd123#wechat_redirect'
                        ],
                        [
                            'type' => 'view',
                            'name' => '商城',
                            'url' => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.env('WX_APPID').'&redirect_uri='.$redirect_uri2.'&response_type=code&scope=snsapi_userinfo&state=asd123#wechat_redirect'
                        ],
                       
                    ]
                ]
            ]
        ];

        $menu_json=json_encode($menu,JSON_UNESCAPED_UNICODE);       //中文要加第二个参数
        $client=new Client();
        $response=$client->request('POST',$url,[
            'body'=>$menu_json
        ]);
        echo '<pre>';print_r($menu);echo '</pre>';
        echo $response->getBody();      //接收微信接口的响应数据
    }

    /**
     * jssdk分享
     */
    public function newYear()
    {
        //dd($_SERVER);
        $wx_appid=env('WX_APPID');
        $noncestr=Str::random(10);
        $timestamp=time();
        $url = env('APP_URL') . $_SERVER['REQUEST_URI'];    //获取当前页面的URL
        $signature=$this->signature($noncestr,$timestamp,$url);
        $data=[
            'appid'=>$wx_appid,
            'timestamp'=>$timestamp,
            'noncestr'=>$noncestr,
            'signature'=>$signature
        ];
        return view('Weixin.newyear',$data);
    }

    /**
     * 计算签名
     */
    public function signature($noncestr,$timestamp,$url)
    {
        $noncestr = $noncestr;
        // 1 获取 jsapi ticket
        $ticket = WxUserModel::getJsapiTicket();
        // 拼接带签名的字符串
        $string1 = "jsapi_ticket={$ticket}&noncestr={$noncestr}&timestamp={$timestamp}&url={$url}";
        // sha1加密
        return  sha1($string1);
    }

   
    
}
