<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 13:48
 */

namespace app\common\exception;


class MissException extends BaseException
{
    public $code = 404;
    public $msg = 'global:your require resource are not found';
    public $errorCode = 10002;

}