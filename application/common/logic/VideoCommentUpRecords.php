<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 18:42
 */

namespace app\common\logic;


use app\model\VideoCommentUpRecords as VideoCommentUpRecordsModel;

class VideoCommentUpRecords
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
        $m = new VideoCommentUpRecordsModel();

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
        $m = new VideoCommentUpRecordsModel();
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
        $m = new VideoCommentUpRecordsModel();
        return $m->insert($data);
    }



}