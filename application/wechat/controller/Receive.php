<?php
namespace app\wechat\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Cookie;
use think\Session;
use app\model\User;
use app\wechat\controller\Base;

class Receive extends Base
{
    
    public function _initialize(){
        parent::_initialize();

    }

    /**
     * 提现页面
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function index(){
        $user = $this->userMsg;
        if(floatval($user['balance']) >= 10){
            $user['canCash'] = 1;
        }else{
            $user['canCash'] = 0;
        }
        $this->assign("data",$user);
        return $this->fetch();
    }
    /**
     *新人一元提现
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function novice(){
        //用户提现
        $user = $this->userMsg;
        $res=['code'=>0];
        //判断领取1元红包
        if($user['oredstatus'] != 1){
            $res['code'] = -1;
            $this->assign("res",$res);
            return $this->fetch("one");
        }
        //判断是否已经领取
        if($user['oredstatus'] == 1 && $user['redcash'] == 1){
            $res['code'] = -2;
            $this->assign("res",$res);
            return $this->fetch("one");
        }
        if($user['oredstatus'] == 1 && $user['redcash'] == 0){
            //判断用户任务完成情况
            $cashTask = ["task_login","usual_read","share_friend_prentice","bind_wechat"];
            $completeMsg = Db::name('user_task_record')->where(['key_code'=>['in',$cashTask],'user_id'=>$user['c_user_id']])->field("number,key_code")->select();
            //后期优化，添加每个任务必须完成次数
            if(count($completeMsg) < 4){
                $res['code'] = -1;
                $this->assign("res",$res);
                return $this->fetch("one");
            }
            //判断微信是否绑定
            $wx_openid =User::where(['c_user_id'=>$user['c_user_id']])->value('wx_openid');
            if(empty($wx_openid)){
                $res['code'] = -1;
                $this->assign("res",$res);
                return $this->fetch("one");
            }
            return $this->fetch();
        } 
    }
  
}