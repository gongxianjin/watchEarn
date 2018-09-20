<?php
namespace app\app\controller;

use think\Request;
use app\app\controller\BaseController;
use app\model\AdSource;
use app\model\AdUser;
use app\model\FConfig;
use app\common\MyController;


class Ad extends  BaseController
{

    public function _initialize(){
        parent::_initialize();
        //用户信息 是否存在
        if($this->user_id){
            
        }
    }
   
    /**
     *获取广告信息
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function getAdMsg(Request $request){
        $user_agent = empty(input("post.user_agent"))?$_SERVER['HTTP_USER_AGENT']:input("post.user_agent");
        $req = $request->param();
        $req['os'] = $this->os;
        $req['version'] = $this->version;
        $req['meid'] = $this->meid;
        $req['user_agent'] = $user_agent;
        //网络情况
        $this->validate($req, [
            'user_agent' => 'require',
            'width' => 'require',
            'height' => 'require',
            'mobile_brand' => 'require',
            'mobile_model' => 'require',
            'mobile_version' => 'require',
            'ip' => 'require',
            'page'=>'require',
            'network_type'=>'require|in:1,2,3,4,5',
        ],[
            'network_type.in'=>"网络错误",
        ]);
        //查询 广告
        //根据类型获取信息
        $ad_count = Fconfig::where(['name'=>"ad_count"])->value("value");
        if($ad_count == 0){
            return out([]);
        }
        $list = AdSource::getSource($ad_count,false,$req);
        if(empty($list)){
            return out([]);
        }
        $adUser = AdUser::getAdUser();
        $adMsg = [];
        foreach ($list as $key => $value) {
           $adUserkeys=array_rand($adUser,1);
            $arr=[
                "id"=> "",
                "title"=> $value['title'],
                "category"=> "",
                "video_url"=> $value['ad_url'],
                "video_duration"=>rand(50000,100000),
                "video_cover"=> ckeckHttp($value['img']['path'])?$value['img']['path']:config("ad_domain").$value['img']['path'],
                "video_height"=> $value['img']['height'],
                "video_width"=> $value['img']['width'],
                "like_count"=> rand(100000,1000000),
                "dislike_count"=> 0,
                "comment_count"=> rand(10000,100000),
                "share_count"=> rand(1000,10000),
                "play_count"=> rand(10000,100000),
                "group_id"=> "",
                "user_id"=> "",
                "user_nickname"=>$adUser[$adUserkeys]['name'],
                "user_avatar"=> $adUser[$adUserkeys]['headpic'],
                "uri"=>"",
                "is_handler_comment"=> 1,
                "create_time"=>date('Y-m-d H:i:s',$value['create_time']),
                "collect_count"=> rand(10000,100000),
                "channel"=>"",
                "top_comments"=>[],
                "is_gold"=>0,
                "is_ad"=>1,
                "is_redpack"=>0,
                "open_browser"=>$value['open_browser'],
                "ad_otherMsg"=>$value['ad_otherMsg'],
                "ad_type"=>$value['ad_type'],
            ];
            $adMsg[] = $arr;
        }
        return out(['data'=>$adMsg]);
    }
    
   
}
