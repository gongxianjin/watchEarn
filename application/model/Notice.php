<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-23
 * Time: 下午2:01
 */

namespace app\model;

use think\Exception;
use think\Model;

class Notice extends Model
{

    const SYSTEM_NOTICE = 1;
    const ALERT_NOTICE = 2;

    public function todayInfo($type)
    {

        if (empty($type))
            throw new Exception("\$type未赋值");

        $time = time();
        return $this
            ->where('type','eq',$type)
            ->where("start_date < {$time} and end_date > {$time}")
            ->select()
            ;
    }

}
