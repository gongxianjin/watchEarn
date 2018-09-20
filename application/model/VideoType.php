<?php
namespace app\model;

use think\Model;

class VideoType extends Model
{

    protected $updateTime = false;

    const V_NORMAL_STATUS  =  1;//正常状态 2 待启用  3禁用

    /**
     * 获取数据
     */
    public static function getLists($type=''){
        $where=[];
        if(!empty($type)){
            $where['type'] = $type;
        }
    	$where['status'] = self::V_NORMAL_STATUS;
    	$order = "sort DESC";
    	$lists = self::where($where)->order($order)->field("id,name,temp_type,type")->order("sort DESC,id ASC")->select();
    	if(!empty($lists))
    		$lists =collection($lists)->toArray();
    	return $lists;
    }


}
