<?php
namespace app\wechat\controller;

use think\Controller;
use think\Request;

class Send extends Controller
{
   /*public function _initialize(){
        parent::_initialize();
    }*/
    /**
     * 首页
     */
    public function index(){
        echo "错误";die;
    }
    public function creatMenu(){
    	$app = config("WX_G_HAO_CONFIG");
    	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$app['app_id']."&secret=".$app['secret'];
    	$res = json_decode(curlhttp($url), true);
    	//var_dump($res);
    	$access_token = $res['access_token'];
    	$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
		$menu =  '{
			"button":[
            {
                "name":"领取",
                "sub_button":[
                {    
                   "type":"view",
                   "name":"兑换提现",
                   "url":"http://wxu.199ju.com/wechat/Receive/index"
                },
                {
                    "type":"view",
                   "name":"新人福利",
                   "url":"http://wxu.199ju.com/wechat/Receive/novice"
                 }
               ]
            },
			{	
				"type":"view",
				"name":"下载",
				"url":"https://android.news.88acca.com/index/Xvideo/download"
			}
			]
		}';
    	$res = json_decode(curlhttp($url,"",$menu,'POST'), true);
    	var_dump($res);die;


    	
    }



}