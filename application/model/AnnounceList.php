<?php
namespace app\model;

use think\Model;

class AnnounceList extends Model{

    const V_NORMAL_STATUS  =  'normal';//正常状态 2 待启用  3禁用

    /**
     * 获取列表
     * where = [];
     */
    public function getList($where = []){

        $where['status'] = self::V_NORMAL_STATUS;

        $lists = self::where($where)->order('order','DESC')->select();

        if(!empty($lists))
            $lists =collection($lists)->toArray();
        return $lists;
    }


}
