<?php

namespace app\model;

use think\Model;

class GoldProductRecord extends Model
{

    /**
     * 获取某用户某类型每日量
     *
     * @param $userId
     * @param $keyCodes
     * @param $startTime
     * @param $endTime
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDailyUsage($userId,$keyCodes,$startTime,$endTime)
    {
        $data = $this
            ->field(['from_unixtime(create_time,\'%Y%m%d\') as date','sum(gold_tribute) as gold','count(1) as count'])
            ->group('from_unixtime(create_time,\'%Y%m%d\')')
            ->where('user_id','eq',$userId)
            ->where('type_key','in',$keyCodes)
            ->where('create_time','between',[$startTime,$endTime])
            ->where('create_type','eq',1)
            ->select();

        return $data;
    }


    /**
     * 获取某用户某类型某段时间的每日量
     *
     * @param $userId
     * @param $keyCodes
     * @param $startTime
     * @param $endTime
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDailyItemUsage($userId,$keyCodes,$startTime,$endTime,$createType,$conditions = '')
    {
        if($conditions){
            $data = $this
                ->field(['from_unixtime(create_time,\'%Y%m%d\') as date','sum(gold_tribute) as gold','count(1) as baseCount'])
                ->group('from_unixtime(create_time,\'%Y%m%d\')')
                ->where('user_id','eq',$userId)
                ->where('type_key','in',$keyCodes)
                ->where('create_time','between',[$startTime,$endTime])
                ->where('create_type','eq',$createType)
                ->having($conditions)
                ->select();
        }else{
            $data = $this
                ->field(['from_unixtime(create_time,\'%Y%m%d\') as date','sum(gold_tribute) as gold','count(1) as baseCount'])
                ->group('from_unixtime(create_time,\'%Y%m%d\')')
                ->where('user_id','eq',$userId)
                ->where('type_key','in',$keyCodes)
                ->where('create_time','between',[$startTime,$endTime])
                ->where('create_type','eq',$createType)
                ->select();
        }

        return $data;
    }

    /**
     * 获取某用户某类型某段时间的参数详情
     *
     * @param $userId
     * @param $keyCodes
     * @param $startTime
     * @param $endTime
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDailyItemDetailUsage($userId,$keyCodes,$startTime,$endTime,$createType,$conditions = '')
    {
        if($keyCodes){
            $data = $this
                ->field(['from_unixtime(create_time,\'%Y%m%d\') as date','min(create_time) as minTime','max(create_time) as maxTime','count(1) as baseCount'])
                ->group('from_unixtime(create_time,\'%Y%m%d\')')
                ->where('user_id','eq',$userId)
                ->where('type_key','in',$keyCodes)
                ->where('create_time','between',[$startTime,$endTime])
                ->where('create_type','eq',$createType)
                ->having($conditions)
                ->select();
        }else{
            $data = $this
                ->field(['from_unixtime(create_time,\'%Y%m%d\') as date','min(create_time) as minTime','sum(gold_tribute) as given_gold','max(create_time) as maxTime','count(1) as baseCount'])
                ->group('from_unixtime(create_time,\'%Y%m%d\')')
                ->where('user_id','eq',$userId)
                ->where('create_time','between',[$startTime,$endTime])
                ->where('create_type','eq',$createType)
                ->having($conditions)
                ->select();
        }

        return $data;
    }



}
