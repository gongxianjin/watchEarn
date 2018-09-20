<?php

namespace app\app\controller;


use think\Request;
use app\common\MyController;


class Gettoken extends MyController
{
    /**
     * 获取token
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function index(){
    	//获取参数
    	$os=input("os");
    	$meid = input("meid");
    	$response_type = "code";
    	$timestamp = input("timestamp");
    	$authkey=input("authkey");
    	$data =[
    		"os"=>$os,
    		"meid"=>$meid,
    		"response_type"=>$response_type,
    		"timestamp"=>input("timestamp"),
    	];
    	$sign = $this->getSign($data);
    	/*if($authkey !== $sign){
    		return out("",1000,"签名信息错误");
    	}else{*/
    		//生成ticket
    		$ticket = $this->createTicket($data);
    		p($ticket);die;
    	//}
    }
    /**
     * 算法
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    private function getSign($data){
		foreach ($data as $key=>$value){
			$arr[$key] = $key; 
		}
		sort($arr); //字典排序的作用就是防止因为参数顺序不一致而导致下面拼接加密不同
		// 2. 将Key和Value拼接
		$str = "";
		foreach ($arr as $k => $v) {
		 $str = $str.$arr[$k].$data[$v];
		}
		//3. 通过sha1加密并转化为大写
		//4. 大写获得签名
		$appsecret = config("app_sign_key");
		$restr=$appsecret.$str;
		$sign = strtoupper(sha1($restr));
		return $sign;
		
    }
    /**
     *生成ticket
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function createTicket($data){
    	$os = $data['os'];
    	$meid = $data['meid'];
    	$sign = "os=".$os."&$meid=".$meid."&time=".time();
        //加密后返回 +  时间限制
    	return $sign;

    }

   
}
