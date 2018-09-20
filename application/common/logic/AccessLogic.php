<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/12
 * Time: 11:47
 */
namespace app\common\logic;



use app\model\AccessStatistics;

class AccessLogic
{
    /**
     * 根据条件查找相应信息
     *
     * @param $data
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function findByCondition($data)
    {
        $m = new AccessStatistics();
        return $m->where($data)->find();
    }

    /**
     * 添加访问记录
     * @param $data
     * @return int|string
     */
    function add($data){
        $m = new AccessStatistics();
        return $m->insert($data);
    }
}