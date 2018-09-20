<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;
use think\Request;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use think\Db;
use app\app\library\UserTackRecord;

class BindWechat extends BaseController implements MissionInterface
{
    protected $goldRun = null;

    function _initGoldRun(&$goldRun)
    {
        $this->goldRun = $goldRun;
    }

    function info()
    {
        exit('info access, exit...');
    }

    function handler()
    {
        if(!Request::instance()->isPost()){
            //返回错误信息
            return out("",10001,"error request");
        }
        if(!$this->login_flag){
            return out("",9999,"please, login in first!");
        }
        $request = request()->param();

        $this->validate($request, [
            'nickname'=>'require',//名称
            'headimg'=>"require",//头像 
            'openid'=>'require',//设备openid
            'unionid'=>'require',//设备openid
        ]);
        if($request['sex'] == 0){
            $request['sex'] = 1;
        }
        if(!in_array($request['sex'], [1,2])){
            return out("",10001,"error request:'sex' field ");
        }
        //判断用户是否已经绑定过微信
        $wx_openid = $this->userModel->where(['c_user_id'=>$this->user_id])->value("wx_openid");
        if(!empty($wx_openid)){
            return out("",10001,"don't try to bind wechat twice");
        }
        //微信是否绑定其他账户
        $otherBind = $this->userModel->where(['wx_openid'=>$request['openid']])->find();
        if(!empty($otherBind)){
             return out("",10001,"wechat can't bind multi account");
        }
        $up=[
            "nickname"=>$request['nickname'],
            "headimg"=>$request['headimg'],
            "wx_openid"=>$request['openid'],
            "sex"=>$request['sex'],
            "unionid"=>$request['unionid'],
        ];
      
        //添加记录到task_invoice
        $logdata=[
          "user_id"=>$this->user_id,
          "task_id"=>$this->goldRun['id'],
          "key_code"=>$this->goldRun['key_code'],
          "status"=>1,
          "num"=>1,
          "sycle"=>$this->goldRun['sycle'],
          "balance"=>0,
          "gold_tribute"=>$this->goldRun['gold_flag'],
          "father_gold_tribute"=>0,
          "grandfather_gold_tribute"=>0,
          "is_del"=>0,
          "description"=>"完成".$this->goldRun['title']."任务",
          "hours_at"=>date('YmdH'),
        ];
        $GoldRunExt = new GoldRunExt();
        Db::startTrans();
        try {
            if(!$this->userModel->where(['c_user_id'=>$this->user_id])->update($up)){
                throw new \Exception('绑定微信号失败'); 
            }
            if(!$GoldRunExt->addTaskLogInvoice($logdata,$this->goldRun)){
                 throw new \Exception('绑定微信号失败');  
            }
            //默认数据
            $goldData = [
                'user_id'=>$this->user_id,
                'gold_tribute'=>$this->goldRun['gold_flag'],
                'status'=>2,
                'type'=>$this->goldRun['id'],
                'type_key'=>$this->goldRun['key_code'],
                'title'=>"完成".$this->goldRun['title']."任务"
            ];
            //接入金币相加系统
            if(!Gold::addUserGold($goldData)){
                throw new \Exception('绑定微信号失败'); 
            }
            if(!UserTackRecord::createRecord($this->user_id,$this->goldRun['key_code'])){
                 throw new \Exception('绑定微信号失败'); 
            }   
            $return['amount'] = $this->goldRun['gold_flag'];
            Db::commit();
            return out($return);
         } catch (\Exception $e) {
             Db::rollback();
            return out("",10001,$e->getMessage());
        } 
    }

}
