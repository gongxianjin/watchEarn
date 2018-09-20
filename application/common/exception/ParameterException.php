<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 11:52
 */

namespace app\common\exception;


class ParameterException extends BaseException
{

    //参数异常错误

    public $code = 400;
    public $errorCode = 10001;
    public $msg = 'invalid paramters';

}