<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/10
 * Time: 13:52
 */

namespace app\common\logic;

use app\model\Retail;

class RetailLogic
{
    /**
     * 根据条件 查找相应数据
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function findByCondition($condition)
    {
        $m = new Retail();

        return $m->where($condition)->find();
    }

    /**
     * 根据条件 查找相应数据
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function findByOrCondition($condition)
    {
        $m = new Retail();
        return $m->whereOr($condition)->find();
    }

    /**
     * 添加数据
     *
     * @param $data
     * @return int|string
     */
    function add($data)
    {
        $m = new Retail();
        return $m->insert($data);
    }
}