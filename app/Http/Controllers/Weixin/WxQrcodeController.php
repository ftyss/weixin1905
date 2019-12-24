<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\WxUserModel;
use GuzzleHttp\Client;

class WxQrcodeController extends Controller
{
    public function qrcode()
    {
        $scene_id=$_GET['scene'];   //二维码的参数
        $access_token=WxUserModel::getAccessToken();
        $url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token.'';  //获取ticket

        $data_a=[
            'expire_seconds'=>604800,
            'action_name'=>'QR_SCENE',
            'action_info'=>[
                'scene'=>[
                    'scene'=>$scene_id
                ]
            ]
       ];

       $client=new Client();
       $response=$client->request('POST',$url,[
           'body'=>json_encode($data_a)
       ]);

       $json1=$response->getBody();
       $ticket=json_decode($json1,true)['ticket'];

       $url='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket.'';

       return redirect($url);
    }
}
