<?php
namespace app\app\controller;

use think\Db;
use think\Request;
use app\app\controller\BaseController;
use app\model\ActivatePush;


class Activate extends BaseController
{
   
    /**
     * 个人中心轮播
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function getActiavtePage(){
        $type = input("type",1);
        
        $list = ActivatePush::where(['status'=>1,"show_type"=>$type])->order("sort DESC")->field("images,jump_url")->limit(5)->select();
        return out($list);
    }
    /**
     * 活动推送
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function push(){
        //新手红包
//        $oredstatus= $this->userInfo["oredstatus"];
//        $oneRed =[];
//        if($oredstatus == 0){
//            $data=[
//                "id"=>0,
//                "title"=>"新手1元红包",
//                "body"=>"新用户注册获得1元新手红包",
//                "picUrl"=>"",
//                "redirect"=>config("web_url_host")."/personal/oneYuanRed.html",
//                "startTime"=>"",
//                "endTime"=>"",
//                "type"=>1,//新人红包类型
//            ];
//        }else{

//        }
        $version = Request::instance()->header('version','1.0.1');
        $meid = Request::instance()->header('meid','');
        $isShow = true;

        //推广商招募弹窗
        $canShowKey = "RetailMeidShow";
        $showMeids = cache($canShowKey);
        //设置符合要求的推广商设备号
        if(empty($showMeids) || true){
            $m = new \app\model\User();
            $showList = $m->field('meid')->where(['status' => 1,'is_cross_read_level' => 0,'total_balance - balance' => ['between',[10,100]]])->select();
//            echo Db::getLastSql();die;
            $showMeids = [];
            foreach ($showList as $val){
                $showMeids[] = $val['meid'];
            }
            cache($canShowKey,$showMeids,['expire' => 3600]);
        }

        //推广商与普通用户分别弹窗
        if(in_array($meid,$showMeids)){
            $list['type'] = 2;
            $list['active_push'] = [
                "picUrl"=>"",
                "redirect"=>""
            ];
            $list['active_push'] = [
                "picUrl"=>config('web_url_host')."/images/alert_retail_2.png",
                "redirect"=>config('web_url_host')."/personal/joinUs.html",
            ];
        }else{
            if(!empty($meid)){
                $cacheKey = 'Active_'.$meid;
                $cache = cache($cacheKey);
                if($cache === 1){
                    $isShow = false;
                }else{
                    cache($cacheKey,1,24 * 3600 * 3);
                }
            }

            $list['type'] = 2;
            $list['active_push'] = [
                "picUrl"=>"",
                "redirect"=>""
            ];
            if($isShow){
                if(version_compare($version,'1.2.1','<')){
                    $list['active_push'] = [
                        "picUrl"=>config('web_url_host')."/images/update-img.png",
                        "redirect"=>"https://play.google.com/store/apps/details?id=com.sven.huinews.international",
                    ];

                }else{
                    $list['active_push'] = [
                        "picUrl"=> config('web_url_host')."/images/notic-img.png",
                        "redirect"=> config("web_url_host")."/personal/howToEarn.html",
                    ];
                }
            }
        }


        return out($list);
    }
    
   
}
