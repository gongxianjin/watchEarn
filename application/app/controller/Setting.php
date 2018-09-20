<?php

namespace app\app\controller;

//use app\app\controller\v1\AuthController;
use think\Request;
use think\Db;
use app\common\MyController;
use app\model\FConfig;

header("Access-Control-Allow-Origin:*");
header('Access-Control-Allow-Method:POST,GET');//允许访问的方式 　
class Setting extends MyController
{
    /**
     * 首次进入app获取app配置信息
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function config(){
        //企业付款方式
        $cash_model =FConfig::where(['name'=>"cash_model"])->value('value');
        $return=[
            "URL"=>[
//                "user_center"=>"http://www.baidu.com",//个人中心
//                "an_apprentice"=>"http://www.baidu.com",//收徒
            ],
            "NEEDCOUNT"=>"999",//需要登录次数
            //分享标题
            "SHARE"=>[
//                "title"=>"看视频还能赚零花,更多精彩，搞笑，整蛊视频....",
//                "content"=>"下载淘视界APP，体验看视频,得金币，赚取零花钱!"
            ],
            "GOlD"=>[
//                'v_at_count'=>3,//金币动画
//                'v_at_red'=>0,//红包
            ],
            "time"=>time(),
            "CMODEL"=>empty($cash_model)?1:intval($cash_model),//提现方式  1 APP  2 微信公众号,
            "IOS" => [
                'DEBUG' => 1,
                "VERSION" => "1.0.0",
                'CONNECT_VERSION' => Request::instance()->header('version')
            ]
        ];
        return out($return);
    }

}
