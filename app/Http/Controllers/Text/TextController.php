<?php

namespace App\Http\Controllers\Text;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

class TextController extends Controller
{
	//
	public function ceshi()	
	{
		echo "hello aaa";       
	}

	public function redis1()
	{
		$key = 'weixin';
		$val = 'hello world';
		Redis::set($key,$val);

		echo time();echo '</br>';
		echo date('Y-m-d H:i:s');
	}


	public function baidu()
    {
        $url = 'https://theory.gmw.cn/2019-12/05/content_33377331.htm';
        $client = new Client();
        $response = $client->request('GET',$url);
        echo $response->getBody();
	}
	

	public function xmlText()
	{
		$xml_str='<xml><ToUserName><![CDATA[gh_dcce247f9947]]></ToUserName>
		<FromUserName><![CDATA[o8rxRwDiVovnSfnWIr--fH2O4_F0]]></FromUserName>
		<CreateTime>1575876652</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[1]]></Content>
		<MsgId>22561128938702669</MsgId>
		</xml>';

		$xml_arr=simplexml_load_string($xml_str);
		echo '<pre>';print_r($xml_arr);echo '</pre>';
	}
}
