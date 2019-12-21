<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use App\Model\WxUserModel;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;


class SendWeatherController extends Controller
{
    public function sendWeather()
    { 
        $openid_arr=WxUserModel::select('openid','nickname','sex')->get()->toArray();
        $openid=array_column($openid_arr,'openid'); 
        //调用群发接口
        $url='https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$this->access_token.'';
        //调用天气接口
        $weather_api='https://free-api.heweather.net/s6/weather/now?location=beijing&key=0eced2803331478083c559f863fe4924';
                $weather_info=file_get_contents($weather_api);
                $weather_info_arr=json_decode($weather_info,true);
                //echo '<pre>';print_r($weather_info_arr);echo '</pre>';die;
                $cond_txt=$weather_info_arr['HeWeather6'][0]['now']['cond_txt'];
                $tmp=$weather_info_arr['HeWeather6'][0]['now']['tmp'];
                $wind_dir=$weather_info_arr['HeWeather6'][0]['now']['wind_dir'];

                $wmsg=$cond_txt .'温度： '.$tmp . '风向： '. $wind_dir;

        $msg = date('Y-m-d H:i:s').$wmsg;

        $data=[
            'touser'=>$openid,
            'msgtype'=>'text',
            'text'=>['content'=>$msg]
        ];

        $client=new Client();
        $response=$client->request('POST',$url,[
            'body'=>json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);

        echo $response->getBody();
    }

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
     * 刷新token
     */
    public function flushAccessToken()
    {
        $key="wexin_access_token";
        Redis::del($key);
        echo $this->getAccessToken();
    }
}
