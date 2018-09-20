<?php
namespace app\app\controller;

/**
 *定时任务处理类
 * @param  模型，引用传递
 * @param  查询条件
 * @param int  每页查询条数
 * @return 返回
 */
use think\Controller;
use think\Request;
use app\model\User;
use app\model\TempUser;
use app\model\Video;
use \app\common\model\Redis;


class Corntab extends Controller
{
    public function ex_corn(){
        $this->getLoginUser();//1分钟前登录过的用户
        $this->pushVideo();//推送视频
    }
    /**
     * 获取1分钟前登录的用户
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function getLoginUser(){
        //前1分钟登录用户        
        $startTime = time()-60;
        $endTime = time()-120;
        $map['update_time'] = ['between',[$startTime,$endTime]];
        $map = "((update_time between $endTime and $startTime) OR (last_login_time  between $endTime and $startTime)) AND  device_tokens <> '' ";
        $userMsg = User::where($map)->field("c_user_id,nickname,device_tokens")->select();
        $tempMsg =  TempUser::where($map)->field("c_user_id,nickname,device_tokens")->select();
        $redis = Redis::instance();
        if(!empty($userMsg)){
            foreach ($userMsg as $key => $value) {
               $redis->rpush('pushToUserVideoLists', json_encode($value));//储存数据
            }
        }
        if(!empty($tempMsg)){
            foreach ($tempMsg as $k => $v) {
               $redis->rpush('pushToUserVideoLists', json_encode($v));//储存数据
            }
        }
        echo "getLoginUser over"."user:".count($userMsg)."temp:".count($tempMsg);
    }
    /**
     * 处理视频推送 针对单个用户
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function pushVideo(){
        $redis = Redis::instance();
        $userMsg=[];
        //获取前100条
        for ($i=0; $i <100 ; $i++) { 
          $r = $redis->lpop("pushToUserVideoLists");
          if(!empty($r)){
            $userMsg[]=$r;
          }
        }
        //获取视频资源
        $videoMsg =Video::where(['title'=>['neq','']])->order("rand()")->find();
        $Um = new Um();
        $pushcount= 0;
        if(!empty($userMsg)){
            $param=[
                "data"=>[
                    "video_id"=>$videoMsg['id']
                ],
            ];
            foreach ($userMsg as $key => &$value) {
                $value = json_decode($value,true);
                //pp($value);die;
                $setting = array(
                    'device_tokens'=>$value['device_tokens'],
                    'ticker'=>"最新最热视频,点击查看",
                    'title'=>$videoMsg['title'],
                    'text'=>"点击查看详情",
                    'after_open'=>"go_app",
                    'actionName'=>"go_app",
                    'actionUrl'=>"",
                    'actionMethodParam'=>json_encode($param),
                    'actionMethod'=>"open_video_detail",
                );
                $re = $Um->pushUnicast($setting); 
                $pushcount++;
            }
        }
        echo "pushVideo over"."push:".$pushcount;
    }

}
