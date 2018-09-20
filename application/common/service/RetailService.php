<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/10
 * Time: 13:51
 */
namespace app\common\service;

use app\common\logic\RetailLogic;

class RetailService
{
    public $retailLogic;

    /**
     * 检测是否申请
     *
     * @param $facebook
     * @param $real_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function checkApply($facebook,$real_id)
    {
        if(empty($this->retailLogic)){
            $this->retailLogic = new RetailLogic();
        }
        //判断三个关键数据是否已经申请
        $params['facebook'] = $facebook;
        $params['real_id'] = $real_id;

        $res = $this->retailLogic->findByOrCondition($params);
        //有数据 则已经申请
        if(empty($res)){
            return true;
        }

        return false;
    }

    /**
     * 添加申请数据
     *
     * @param $data
     * @return bool
     */
    function addApply($data)
    {
        if(empty($this->retailLogic)){
            $this->retailLogic = new RetailLogic();
        }
        $res = $this->retailLogic->add($data);
        if(empty($res)){
            return false;
        }
        return true;
    }
}