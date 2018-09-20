<?php

namespace app\app\controller;

use app\model\ShareVisit;
use think\Request;

use app\app\controller\BaseController;

use app\model\User;

use app\model\TempUser;

use app\model\TaskInvoice;

use app\model\GoldRun;

use app\model\UserTaskRecord;

use think\Db;

class Redbag extends BaseController
{

    /**
     * 领取红包
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */

    public function getOneRed(){
        if($this->login_flag){
            $userModel = new User();
        }else{
            $userModel = new TempUser();
        }

        $newNews =  1;//新人红包金额
        $redMsg =GoldRun::where(['key_code'=>'new_one_red'])->find();

        $userInfo = $this->userInfo;
        $TaskInvoice = new TaskInvoice();
        $is_exists_record =$TaskInvoice->where(['user_id'=>$this->user_id,"key_code"=>"new_one_red"])->find();
        if($userInfo['oredstatus'] == 0 && empty($is_exists_record)){

             $up=[
                "oredstatus"=>1,
                "balance"=>['exp',"balance+$newNews"],
                "total_balance"=>['exp',"total_balance+$newNews"],
            ];

            $userModel->where(['c_user_id'=>$this->user_id])->update($up);

            $task = [
                "user_id"=>$this->user_id,
                "task_id"=>$redMsg['id'],
                "key_code"=>$redMsg['key_code'],
                "status"=>1,//已完成
                "num"=>1,//
                "create_time"=>time(),
                "update_time"=>time(),
                "balance"=>1,
                "is_del"=>1,
                "type"=>$redMsg['type'],
                "hours_at"=>date('YmdH',time()),
            ];

            //记录到用户任务记录表
            TaskInvoice::create($task);

            //记录到用户交易表
            $log=[
                "user_id"=>$this->user_id,
                "balance"=>$newNews,
                "title"=>"新手1元红包",
                "create_time"=>time(),
                "type"=>2,
            ];

            Db::name("balance_log")->insert($log);
            $return['amount'] = 1;
            return out($return);

        }

        return out("",10001,"您已领取过该福利");
    }

    /**
     * 红包页面提现任务判断
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */

    public function cashOneRedTisk(){

        $UserTaskRecord= new UserTaskRecord();

        $openid = $this->userModel->where(['c_user_id'=>$this->user_id])->value("wx_openid");

        $task=[
            'usual_read'=>["total"=>1, "number"=>0,"finished"=>false],
            'share_friend_prentice'=>["total"=>1,"number"=>0,"finished"=>false],
        ];

        $cashTask = ["usual_read","share_friend_prentice"];

        $completeMsg = $UserTaskRecord->where(['key_code'=>['in',$cashTask],'user_id'=>$this->user_id])->field("number,key_code")->select();

        $finished=0;

        if(!empty($completeMsg)){
            foreach ($completeMsg as $key => $value) {
                if($value['number']>0){
                    $finished++;
                    $task[$value['key_code']]['number']=1;
                    $task[$value['key_code']]['finished']=true;
                }
            }
        }

        //是否绑定微信
        $return['total_task']=count($task);
        $return['success_wx']=empty($openid)?false:true;
        $return['finished']=$finished;
        $return['task']=$task;
        return out($return);

    }

}

