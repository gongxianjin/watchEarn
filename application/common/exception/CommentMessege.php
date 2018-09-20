<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 14:06
 */

namespace app\common\exception;


class CommentMessege extends BaseException
{

    public $code = 401;
    public $msg = 'comment is not exist';
    public $errorCode = 100001;

}