<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-22
 * Time: 下午8:47
 */

namespace app\model;

use think\Model;

class FConfig extends Model
{
	//视频列表需要数据
	static public $video_need_msg=[
		"redpack_count",//红包
		"ad_count",//广告
		"gold_count",//金币
		"day_read_get_count",//每天可获取金币，红包总数量
		"ad_display_model",//广告展示方式
		"ad_position",//广告位置
	];
	/**
	 * 查询指定内容
	 * @param  模型，引用传递
	 * @param  查询条件
	 * @param int  每页查询条数
	 * @return 返回
	 */
	static public function getVideolistMsg(){
		$list = self::where(['name'=>['in',self::$video_need_msg]])->column("value","name");
	
		return $list;
	}


}