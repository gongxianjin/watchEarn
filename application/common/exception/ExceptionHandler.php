<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 11:31
 */

namespace app\common\exception;

use Exception;

use think\exception\Handle;
use think\Request;

class ExceptionHandler extends Handle
{

    private $code;
    private $msg;
    private $errorCode;

    public function render(Exception $e)
    {

        if($e instanceof BaseException){

            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;

        }else{

            if(config('app_debug')){
                return parent::render($e);
            }

            $this->code = 500;
            $this->msg = '服务器内部错误';
            $this->errorCode = 999;
        }

        $request = Request::instance();
        $result = [
            'code'=> $this->errorCode,
            'msg' => $this->msg,
            'request_url' => $request->url()
        ];

        return json($result,$this->code);
    }
}