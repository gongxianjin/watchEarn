<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 11:50
 */

namespace app\common\exception;


use think\Exception;

class BaseException extends Exception
{

    public $code = 400;

    public $msg = '参数错误';

    public $errorCode = 10000;


    /**
     *
     * 构造函数 接收一个关联数组
     *
     * */

    public function __construct($params = [])
    {
        if(!is_array($params)){
            return;
        }

        if(array_key_exists('code',$params)){
            $this->code = $params['code'];
        }

        if(array_key_exists('msg',$params)){
            $this->msg = $params['msg'];
        }

        if(array_key_exists('code',$params)){
            $this->errorCode = $params['errorCode'];
        }
    }


}