<?php
namespace app\wechat\controller;

use app\wechat\controller\Base;

use think\Request;
use think\Db;
use think\Validate;
use app\model\User;
use app\model\UserCashRecord;
use Payment\WxCash;

class Usercenter extends Base
{
	public $userInfo=[];
   	public function _initialize(){
        parent::_initialize();
    	//查询用户
    	$user = User::where(['unionid'=>$this->unionid])->find();
    	if(empty($user['web_openid'])){
    		User::where(['c_user_id'=>$user['c_user_id']])->update(['web_openid'=>$this->openid]);
    	}
    	$this->userInfo = $user;
    }
    /**
     * 首页
     */
    public function index(){
    	$user = $this->userInfo;
    	$res=['code'=>0];
    	if(empty($user)){
    		$res['code'] = -1;
    		$this->assign("res",$res);
    		return $this->fetch("one");
    	}
    	//判断领取1元红包
    	if($user['oredstatus'] != 1){
    		$res['code'] = -1;
    		$this->assign("res",$res);
    		return $this->fetch("one");
    	}
    	//1元提现
    	if($user['oredstatus'] == 1 && $user['redcash'] == 0){
    		//判断用户任务完成情况
		      $cashTask = ["task_login","usual_read","share_friend_prentice","bind_wechat"];
		      $completeMsg = Db::name('user_task_record')->where(['key_code'=>['in',$cashTask],'user_id'=>$user['c_user_id']])->field("number,key_code")->select();
		      //后期优化，添加每个任务必须完成次数
		      if(count($completeMsg) < 4){
		          $res['code'] = -2;
		      }
		      //判断微信是否绑定
		      $wx_openid =User::where(['c_user_id'=>$user['c_user_id']])->value('wx_openid');
		      if(empty($wx_openid)){
		           $res['code'] = -2;
		      }
		      if($res['code'] === 0){
		      	$userCashRecord = new UserCashRecord();
		      	$order = $userCashRecord->createRecord([
			        "user_id"=>$user['c_user_id'],
			        "amount"=>1,
			        "openid"=>$this->openid,
			        "nickname"=>$user['nickname'],
			        'desc'=>"聚编号" . time(),
			        'type'=>1,
			        "cashmodel"=>2,
			      ]);
			      if(!empty($order)){
				      	$WxCash = new WxCash();
				      	$cashres = $WxCash->cash($order);
				      	$up=["draw_flag"=>$cashres['data']['draw_flag'],"err_code_des"=>$cashres['data']['err_code_des']];
					    if($cashres['code'] == 200){
					          $up['state'] =1;
					          $up['pay_time'] = time();
					          //一元提现
					          (new InvitedApprenticeWithdraw())->run($user);
					    }else{
					           $up['state'] = 2;
					    }
					    $res['code'] = 1;//成功
						$userCashRecord->updateField($order['id'],$up);
						if($up['state'] == 1){
							$user_up['redcash'] = 1;
							$user_up['balance'] = ['exp',"balance-$oneRed"];
							$this->userModel->where(['c_user_id'=>$user['c_user_id']])->update($user_up);
						}else{
							$res['code'] = -3;
						}
			      }else{
			      	$res['code'] = -4;
			      }
		      }
		     	$this->assign("res",$res);
    			return $this->fetch("one");
    	}
    	//已完成1元提现
    	if($user['oredstatus'] == 1 && $user['redcash'] == 1){
    		if(floatval($user['balance']) >= 10){
    			$user['canCash'] = 1;
	    	}else{
	    		$user['canCash'] = 0;
	    	}
    		$this->assign("data",$user);
    		return $this->fetch("index");
    	}
    }
   	/**
   	 * 用户提现
   	 * @param  模型，引用传递
   	 * @param  查询条件
   	 * @param int  每页查询条数
   	 * @return 返回
   	 */
   	public function cash(){
   		if(!Request::instance()->isPost()){
            //返回错误信息
            exit("错误操作");
        }
        $userInfo = $this->userInfo;
        $request = request()->param();
		$validate = new Validate([
		     '__token__'  =>  'require|token',
		]);
		$data = [
		    '__token__'  => input("__token__"),
		];
		$res['code'] = 0;
		if (!$validate->check($data)) {
		  	$res['code'] = -2;
		  	$this->assign("res",$res);
        	return $this->fetch('res');
		}
        $amount = isset($request['amount'])?$request['amount']:0;
        if(!preg_match('/^[1-9]{1}+([0-9])?+(.[0-9]{1,2})?$/', $amount)) {
            $res['code'] = -1;
        }
        if($amount <= 0){
        	$res['code'] = -1;
        }
        $balance = $userInfo['balance'];
        if($amount>$balance){
        	$res['code'] = -1;
        }
        //判断微信是否绑定
        $wx_openid = User::where(['c_user_id'=>$userInfo['c_user_id']])->value('wx_openid');
        if(empty($wx_openid)){
           	$res['code'] = -1;
        }
        if($res['code'] ===0){
        	 Db::startTrans();
	        try {
	             //生成订单信息
	            $userCashRecord = new UserCashRecord();
	            $order = $userCashRecord->createRecord([
	              "user_id"=>$userInfo['c_user_id'],
	              "amount"=>$amount,
	              "openid"=>$this->openid,
	              "nickname"=>$userInfo['nickname'],
	              'desc'=>"聚编号" . time(),
	              'type'=>2,
	              "cashmodel"=>2,
	            ]);
	            if(empty($order)){
	              throw new \Exception("提现操作失败");
	            }
	            //减去用户金额，冻结申请提现金额
	            $up['balance'] = ['exp',"balance-$amount"];
	            $up['frozen_balance'] = ['exp',"frozen_balance+$amount"];
	            if(!User::where(['c_user_id'=>$userInfo['c_user_id']])->update($up)){
	              throw new \Exception("提现操作失败");
	            }
	            Db::commit();
	            $res['amount'] = $amount;
	       		$res = ['code'=>1];
	        } catch (\Exception $e) {
	          	Db::rollback();
	          	$res = ['code'=>-1];
	        }
        }
        $this->assign("res",$res);
        return $this->fetch('res');
   	}
}