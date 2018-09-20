<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-23
 * Time: 下午2:01
 */

namespace app\model;

use think\Model;

class UserTaskRecord extends Model
{

    /**
     * 该记录是否已存在
     *
     * @param $userId user -> c_user_id
     * @param $keyCode
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function once($userId,$keyCode)
    {
        return $this->where(['user_id'=>$userId,"key_code"=>$keyCode])->field('id')->find();
    }

    /**
     * @param $userId
     * @param $keyCode
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function todayOnce($userId,$keyCode)
    {
        $start_time = mktime(0,0,0);
        $end_time = mktime(23,59,59);
        return $this
            ->where(['user_id'=>$userId,"key_code"=>$keyCode])
            ->where("update_time > {$start_time} and update_time < {$end_time}")
            ->field('id')->find();
    }


}
