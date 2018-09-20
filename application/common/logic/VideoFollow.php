<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 13:25
 */

namespace app\common\logic;

use app\model\VideoFollow as VedioFollowModel;

class VideoFollow
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
        $m = new VedioFollowModel();

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
        $m = new VedioFollowModel();
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
        $m = new VedioFollowModel();
        return $m->insert($data);
    }

}