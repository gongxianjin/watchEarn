<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-24
 * Time: 下午3:24
 */

namespace app\model;

use think\Model;

class Grade extends Model
{
    protected function getCreateDateAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['create_time']);
    }
    /**
     * 计算等级
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function userGrade($apprentice_total){
    	$list = $this->order('need_apprentice_num', 'asc')->select();
    	$count = count($list)-1;
    	foreach ($list as $key => $value) {
    		if($key == $count ){
    			$and = "  true";
    		}else{
    			$and =  $list[$key+1]['need_apprentice_num'] > $apprentice_total;
    		}
    		if($value['need_apprentice_num'] <= $apprentice_total &&   $and){
    			return $value;
    				continue;
    		}
    	}
    	return [];
    }


}