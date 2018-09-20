<?php

namespace app\app\controller;

use app\model\BalanceLog;

use think\Request;

class Balance extends BaseController
{
    public function log()
    {
        $limit =input("limit/d",10);
        $log = BalanceLog::where(['user_id'=>$this->user_id])->order("id DESC")->paginate($limit)->toArray();
        foreach ($log['data'] as &$item)
        {
            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
        }
        return out($log['data']);

    }
}

