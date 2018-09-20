<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/17
 * Time: 14:34
 */

namespace app\common\logic;

use app\model\NewVideo;
use app\model\UserVideo;

class UserVideoLogic
{
    public $model;

    function __construct()
    {
        //单例模式model
        if(!is_object($this->model)){
            $this->model = new UserVideo();
        }
    }

    /**
     * 添加用户上传视频
     *
     * @param $data
     * @return int|string
     */
    function add($data)
    {
        return $this->model->insert($data);
    }

    /**
     * 根据条件筛选单个视频
     *
     * @param $condition
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function findByCondition($condition)
    {
        return $this->model->where($condition)->find();
    }

    /**
     * 根据条件获取列表
     *
     * @param array $condition
     * @param int $page
     * @param int $pageSize
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function getListByCondition($condition=[],$page=1,$pageSize=20,$order='create_time desc')
    {
        return $this->model->where($condition)->select();
    }

    /**
     * 根据条件获取虚拟视频列表
     */
    public function getDuListByCondition($condition = []){

        $model = new NewVideo();
        return $model->where($condition)->select();
    }
}