<?php

namespace app\model;

use think\Model;

class InvitedApprenticeDailyRecord extends Model
{

    /**
     * 获取某日是否完成
     *
     * @param $userId
     * @param $time
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDailyUsage($userId,$time)
    {
        $date = date('Ymd',$time);
        $exists = $this
            ->field('date')
            ->where('user_id','eq',$userId)
            ->where('date','eq',$date)
            ->find();

        return $exists;
    }

}
