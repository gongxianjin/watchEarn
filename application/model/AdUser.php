<?php
namespace app\model;

use think\Model;

/**
 *广告域名
 * @param  模型，引用传递
 * @param  查询条件
 * @param int  每页查询条数
 * @return 返回
 */
class AdUser extends Model
{

	
	static public function getAdUser(){
		$list =  self::order("rand() DESC")->limit(20)->select();
		return $list;
	}
	
	
}