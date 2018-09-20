<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-23
 * Time: 下午4:25
 */

namespace app\app\library;

use app\model\GoldRun;
use app\model\UserTaskRecord;
use time\TimeUtil;
use think\Db;
use app\app\library\GoldRunExt;
use app\app\library\Gold;

class Wxbind
{
    /**
     * 记录用户活动或任务情况
     * @param int $user_id 用户id
     * @param string key_code 任务或者活动key_code 
     * @return array
     */
    public static function wxbind($user_id, $key_code= "bind_wechat")
    {

        $wechatConfig = Db::name("gold_run")->where(['key_code'=>$key_code])->find();
        $res = ['code'=>-1,"msg"=>"绑定失败"];
        if(empty($wechatConfig)){
            $res['msg']="任务不存在";
            return $res;
        }
        //添加记录到task_invoice
        $logdata=[
          "user_id"=>$user_id,
          "task_id"=>$wechatConfig['id'],
          "key_code"=>$wechatConfig['key_code'],
          "status"=>1,
          "num"=>1,
          "sycle"=>$wechatConfig['sycle'],
          "balance"=>0,
          "gold_tribute"=>$wechatConfig['gold_flag'],
          "father_gold_tribute"=>0,
          "grandfather_gold_tribute"=>0,
          "is_del"=>0,
          "description"=>"完成".$wechatConfig['title']."任务",
          "hours_at"=>date('YmdH'),
        ];
        $GoldRunExt = new GoldRunExt();
        try {
            if(!$GoldRunExt->addTaskLogInvoice($logdata,$wechatConfig)){
                 throw new \Exception('绑定微信号失败');  
            }
            $param=[
               "user_id"=>$user_id,
                "type"=>$wechatConfig['id'],
                "gold_tribute"=> $wechatConfig['gold_flag'],
                "status"=>1,//暂不发放
                "type_key"=>$wechatConfig['key_code'],
                "title"=>"完成".$wechatConfig['title']."任务",
            ];
            //接入金币相加系统
            if(!Gold::addUserGold($param)){
                throw new \Exception('绑定微信号失败'); 
            }
            if(!UserTackRecord::createRecord($user_id,"bind_wechat")){
                throw new \Exception('绑定微信号失败');
            }
            $res['code'] = 200;
            $res['msg']="绑定成功";
            $res['amount'] = $wechatConfig['gold_flag'];
         } catch (\Exception $e) {
            $res['msg'] = $e->getMessage();
        } 
        return $res;
    }

}