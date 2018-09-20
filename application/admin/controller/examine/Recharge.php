<?php

namespace app\admin\controller\examine;

use app\app\library\Gold;
use app\common\controller\Backend;
use app\model\User;
use think\Controller;
use think\Request;
use think\Db;
//use app\model\Video;
use app\model\UserCashRecord;
use Payment\WxCash;

/**
 * 用户充值
 *
 * @icon fa fa-circle-o
 */
class Recharge extends Backend
{


    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 查看
     */
  public function index()
{
    if ($this->request->isAjax())
    {
        $email = input('post.email','');
        if(empty($email)){
            return out([],10002,'请输入充值邮箱！');
        }

        $pwd = input('post.pwd','');
        if(empty($pwd) || $pwd != "hkzy123"){
            return out([],10002,'充值密码错误！');
        }

        $desc = input('post.desc','');
        if(empty($desc)){
            return out([],10002,'请输入充值备注！');
        }

        $num = intval(input('post.num',0));
        if(empty($num) || $num < 0){
            return out([],10002,'请输入充值数量！');
        }

        $userModel = new User();
        $rechargeUser = $userModel->where(['mail' => $email])->find();
        if(empty($rechargeUser)){
            return out([],10002,'用户不存在！');
        }
        if($rechargeUser['status'] !=1 || $rechargeUser['is_cross_read_level'] !=0) {
            return out([],10002,'用户被封禁或封号！');
        }

        //默认数据
        $goldData = [
            'user_id'=>$rechargeUser['c_user_id']
            ,'gold_tribute'=>$num
            ,'status'=>2
            ,'type'=>0
            ,'type_key'=>$desc
            ,'title'=>$desc
            ,'type_task_id'=>0
            ,'father_gold_tribute'=> 0
            ,'grandfather_gold_tribute'=>0
            ,'tribute_status'=>2
            ,'tribute_title'=>""
            ,'func'=>""
        ];
        $gold = Gold::addUserGold($goldData);
        if($gold > 0){
            return out([],200,'充值成功！');
        }
        return out([],10002,'充值失败！');
    }
    return $this->view->fetch();
}
   
   
    

}
