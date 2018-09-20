<?php
namespace app\model;

use think\Model;
use think\Db;

class UserCashRecord extends Model
{

	protected $autoWriteTimestamp = false;
    const WX_KAIFANG = 1;//开放平台提现
    const WX_GONGZHONG = 2;//微信公众号提现
    const PAYPAY_REMITTANCE = 3; // paypal汇款

 	/**
     * 生成订单
     */
    public  function createRecord($array){
        //判断新手一元提现是否存在记录
        $data = [
            "user_id"=>$array['user_id'],
            "order_number"=>$this->createOrder("D"),
            "amount"=>$array['amount'],
            "state"=>0,
            "create_time"=>time(),
            "paypal_mail"=>$array['paypal_mail'],
            "nickname"=>$array['nickname'],
            "desc"=>$array['desc'],
            "wx_desc"=>"提现申请",
            "type"=>$array['type'],
            "cashmodel"=>$array['cashmodel'],
        ];
        if($this->save($data)){
            return  $this->getOrderRcord($data['order_number']);
        }else{
            return [];
        }
    }
    /**
     * 获取订单信息
     */
    public function getOrderRcord($order_number){
        $data = $this->where(['order_number'=>$order_number])->find();
        return $data;
    }

    /**
     * 获取订单信息
     */
    public function getOrderRcordByid($id){
        $data = $this->where(['id'=>$id])->find();
        return $data;
    }
    /**
     * 生成订单号（暂不需要）
     */
    private function  createOrder($suffix){
    	return $suffix.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        //$number = build_order_sn("ld");
    }
    
    /**
     * 修改记录
     */
    public function updateField($id,$data){
        return $this->where(['id'=>$id])->update($data);
    }
   
 	

}
