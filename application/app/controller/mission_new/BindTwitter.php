<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;
use app\model\GoldProductRecord;
use think\Request;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use think\Db;
use app\app\library\UserTackRecord;

class BindTwitter extends BaseController implements MissionInterface
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

        $user = &$this->userInfo;

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
            'twitter_id'=>'require',//设备openid
        ]);

        //twitter 不能获取性别 默认为男
        $request['sex'] = 1;

        if(!in_array($request['sex'], [1,2])){
            return out("",10001,"error request:'sex' field ");
        }

        $isFinish = GoldProductRecord
            ::where('type_key','eq',$this->goldRun['key_code'])
            ->where('user_id','eq',$user['c_user_id'])
            ->value("id") == null ? false:true;

        //判断用户是否已经绑定过Twitter
        $twitter_id = $this->userModel->where(['c_user_id'=>$this->user_id])->value("twitter_id");
        if(!empty($twitter_id) && $isFinish ){
            return out("",10001,"already linked twitter");
        }

        //微信是否绑定其他账户
        $otherBind = $this->userModel->where(['twitter_id'=>$request['twitter_id']])->where('c_user_id','neq',$this->user_id)->find();
        if(!empty($otherBind) ){
            return out("",10001,"twitter already linked another account");
        }

        $up=[
            "nickname"=>$request['nickname'],
            "headimg"=>$request['headimg'],
            "twitter_id"=>$request['twitter_id'],
            "sex"=>$request['sex'],
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
            "description"=>$this->goldRun['title'],
            "hours_at"=>date('YmdH'),
        ];
        $GoldRunExt = new GoldRunExt();
        Db::startTrans();
        try {
            $this->userModel->where(['c_user_id'=>$this->user_id])->update($up);
            $GoldRunExt->addTaskLogInvoice($logdata,$this->goldRun);
            //默认数据
            $goldData = [
                'user_id'=>$this->user_id,
                'gold_tribute'=>$this->goldRun['gold_flag'],
                'status'=>2,
                'type'=>$this->goldRun['id'],
                'type_key'=>$this->goldRun['key_code'],
                'title'=>$this->goldRun['title']
            ];
            //接入金币相加系统
            if(!Gold::addUserGold($goldData)){
                throw new \Exception('bind wechat failed');
            }
            if(!UserTackRecord::createRecord($this->user_id,$this->goldRun['key_code'])){
                throw new \Exception('bind wechat failed');
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
