<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 11:35
 */
namespace app\common\logic;

use app\model\Tag;

class TagLogic
{
    public $model;

    function __construct()
    {
        //单例模式model
        if(!is_object($this->model)){
            $this->model = new Tag();
        }
    }

    /**
     * 根据条件获得数据
     *
     * @param $condition
     * @param $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function getByCondition($condition,$order)
    {
        return $this->model->where($condition)->order($order)->select();
    }
}