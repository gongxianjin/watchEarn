<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2
 * Time: 14:15
 */

namespace app\app\behavior;

class Check
{
    function run()
    {
        //真实ip地址为 $_SERVER['REMOTE_ADDR']
        if(!empty($_SERVER['HTTP_VIA']) || !empty($_SERVER['HTTP_X_FORWARDED_FOR']))    //使用了代理
        {
            pjson(['code' => 10002,'msg' => 'Non - use agent','data' =>[]]);
            die;
        }
    }
}