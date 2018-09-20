<?php
namespace app\model;

use think\Model;

class Music extends Model
{

    protected $updateTime = false;

    const V_NORMAL_STATUS  =  'normal';//正常状态 2 待启用  3禁用

    /**
     * 获取数据
     */
    public static function getLists($type='',$page = 1,$pageSize = 20){
        $where=[];
        if(!empty($type)){
            $where['type_id'] = $type;
        }
    	$where['status'] = self::V_NORMAL_STATUS;
    	$order = 'order DESC';
    	$lists = self::where($where)->order('"'.$order.'"')->page($page,$pageSize)->select();

    	if(!empty($lists))
    		$lists =collection($lists)->toArray();
    	return $lists;
    }


}
