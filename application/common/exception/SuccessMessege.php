<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 14:06
 */

namespace app\common\exception;


class SuccessMessege extends BaseException
{

    public $code = 201;
    public $msg = 'ok';
    public $errorCode = 200;

}