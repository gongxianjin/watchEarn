<?php
namespace app\app\controller;

use app\app\controller\mission_new\InvitedApprenticeWithdraw;
use app\common\model\Redis;
use app\model\BalanceLog;
use app\model\ConvertLog;
use app\model\FConfig;
use think\Request;
use app\app\controller\BaseController;
use app\model\User;
use app\model\TempUser;
use app\model\UserTaskRecord;
use think\Db;
use app\model\UserCashRecord;
use Payment\WxCash;

class Cash extends BaseController
{

    private static $EXCHANGE_RATE_NAME = 'exchange_rate';

    /**
     * 用户申请提现
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function applyPay(){
        if(!Request::instance()->isPost()){
            //返回错误信息
            return out("",10001,"error post");
        }
//        $userInfo = $this->userInfo;
        $userInfo = Db::name('user')->where(['c_user_id' => $this->user_id])->find();
        if(empty($userInfo) || !$this->login_flag){
            return out("",9999,"please sign in");
        }
        $request = request()->param();
        $amount = isset($request['amount'])?$request['amount']:0;
        if( $amount >= 100) {
            return out("",10001,"wrong number");
        }
        if($amount < 5){
             return out("",10001,"Withdraw amount no less than 5");
        }
        if ($amount != intval($amount))
        {
            return out("",10001,"amount without decimals");
        }
        $balance = $userInfo['balance'];
        if($amount>$balance){
          return out("",10001,"not enough balance, make more friends will earn more");
        }
        //判断是否绑定paypal
        $paypalMail = $this->userModel->where(['c_user_id'=>$this->user_id])->value('paypal_mail');
        if(empty($paypalMail)){
            return out("",10001,"please bind your paypal mail/phone/id");
        }
        Db::startTrans();
        try {
             //生成订单信息
            $userCashRecord = new UserCashRecord();
            $order = $userCashRecord->createRecord([
              "user_id"=>$this->user_id,
              "amount"=>$amount,
              "paypal_mail"=>$paypalMail,
              "nickname"=>$userInfo['nickname'],
              'desc'=>"Withdraw",
              'type'=>2,
              "cashmodel"=>$userCashRecord::PAYPAY_REMITTANCE,
            ]);
            if(empty($order)){
              throw new \Exception("Withdraw error!");
            }

            //添加账户流水
            $balanceLog = [
                'user_id' => $this->user_id,
                'balance' => -1 * $amount,
                'title' => 'Withdraw',
                'create_time' => time(),
                'type' => 3
            ];

            $banlanceModel = new BalanceLog();
            $res = $banlanceModel->insert($balanceLog);
            if(!$res){
                throw new \Exception("Withdraw error!");
            }

            //减去用户金额，冻结申请提现金额
            $up['balance'] = ['exp',"balance-$amount"];
            $up['frozen_balance'] = ['exp',"frozen_balance+$amount"];
            if(!$this->userModel->where(['c_user_id'=>$this->user_id])->update($up)){
              throw new \Exception("Withdraw error!");
            }
            Db::commit();
        } catch (\Exception $e) {
          Db::rollback();
          return out("",10001,$e->getMessage());
        }
        $return['amount'] = $amount*100;
        return out($return,200,"withdraw request send, please wait for checking");

    }

    public function exchange()
    {

//        $userInfo = &$this->userInfo;
        $userInfo = Db::name('user')->where(['c_user_id' => $this->user_id])->find();
        $exchangeRate = FConfig::where('name','eq',self::$EXCHANGE_RATE_NAME)->value('value');
        if (empty($exchangeRate))
            return out([],'10002',"更新中，请稍等");

        if ($userInfo['gold_flag']<$exchangeRate)
            return out([],'10002',"至少{$exchangeRate}金币才能兑换");

        // 剩余金币
        $leftGold = $userInfo['gold_flag']%$exchangeRate;
        // 被转化的金币
        $convertGold = $userInfo['gold_flag'] - $leftGold;
        // 增加的余额
        $incrementBalance = $convertGold/$exchangeRate/100;

        $userModel = $this->login_flag == true ? new User():new TempUser();

        try{
            Db::startTrans();

            // 添加余额增加记录
            BalanceLog::insert([
                'balance'=>$incrementBalance
                ,'user_id'=>$userInfo['c_user_id']
                ,'title'=>'Convert gold to Balance'
                ,'create_time'=>time()
                ,'type'=>1
            ]);

            // 添加转换日志
            ConvertLog::insert([
                'user_id'=>$userInfo['c_user_id']
                ,'source'=>$userInfo['gold_flag']
                ,'conveted'=>$convertGold
                ,'left'=>$leftGold
                ,'balance'=>$incrementBalance
                ,'log_time'=>time()
            ]);

            // 增加用户余额 设置金币
            $userModel->update([
                'gold_flag'=>$leftGold
                ,'balance'=>['exp','balance+'.$incrementBalance]
                ,'total_balance'=>['exp','total_balance+'.$incrementBalance]
            ],[
                'c_user_id'=>$userInfo['c_user_id']
            ]);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            return out([],'10002','兑换失败');
        }

        $data= [
            'left_gold'=>$leftGold
            ,'balance'=>sprintf('%.2f',$userInfo['balance']+$incrementBalance)
            ,'convert_gold'=>$convertGold
            ,'increment_balance'=>$incrementBalance
        ];

        return out($data,'200','exchanged');
    }

}
