<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/12
 * Time: 11:46
 */
namespace app\common\service;

use app\common\logic\AccessLogic;

class AccessService
{
    function addData($user_id,$access,$title,$ip)
    {
        $logic = new AccessLogic();
        $data['user_id'] = $user_id;
        $data['access'] = $access;
        $data['ip'] = $ip;
        $info = $logic->findByCondition($data);
        if(!empty($info)){
            $info->count = $info->count + 1;
            $info->save();
            return true;
        }

        $data['access_address'] = $title;
        $data['count'] = 1;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $logic->add($data);
        return true;
    }
}