<?php
namespace app\app\controller;

use think\Request;
use umNotification;
use think\Hook;
use think\Db;
use app\app\library\Gold;
use app\model\Domain;
use app\model\Grade;
use \app\common\model\Redis;
use app\common\MyController;


class Um extends MyController
{
    private $notification=null;
    public function __construct()
    {
         $this->notification = new umNotification("5a6fe152b27b0a4325000084","btgfl0cprtugnrd5s25pktbfezrousxc");
    }

    /**
     *视频推送,单个用户推送
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function  pushUnicast($data){
          $setting = array(
                'device_tokens'=>$data['device_tokens'],
                'ticker'=>isset($data['ticker'])?$data['ticker']:"最新最热视频,点击查看",
                'title'=>isset($data['title'])?$data['title']:"最新最热视频,点击查看",
                'text'=>isset($data['text'])?$data['text']:"点击查看详情",
                'after_open'=>"go_app",
                'actionName'=>"go_app",
                'actionUrl'=>"",
                'actionMethodParam'=>isset($data['actionMethodParam'])?$data['actionMethodParam']:"",
                'actionMethod'=>isset($data['actionMethod'])?$data['actionMethod']:"",
            );
          if(strlen($data['device_tokens']) == 44){
                $re = $this->notification->sendAndroidUnicast($setting);
          }else{
                $re = $this->notification->sendIOSUnicast($setting);
          }
          return $re;
    }
    /**
     * 广播  默认每天可推送10次
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function Broadcast($data){
        $setting = array(
                'ticker'=>isset($data['ticker'])?$data['ticker']:"最新最热视频,点击查看",
                'title'=>isset($data['title'])?$data['title']:"最新最热视频,点击查看",
                'text'=>isset($data['text'])?$data['text']:"点击查看详情",
                'after_open'=>"go_app",
                'actionName'=>"go_app",
                'actionUrl'=>"",
                'actionMethodParam'=>json_encode($data),
                'actionMethod'=>"open_video_detail",
            );
            $re = $this->notification->sendAndroidBroadcast($setting);
            return $re;
            //$re = $this->notification->sendIOSBroadcast($setting);
          
    }
  
}
