<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 10:57
 */

namespace app\common\validate;


use app\common\exception\ParameterException;
use think\Exception;
use think\Validate;

class BaseValidate extends Validate
{

    public function goCheck($params){

        if(!$this->check($params)){

            $exception = new ParameterException([
                'msg'=>is_array($this->error)?implode(';',$this->error):$this->error,
            ]);

            throw $exception;

        }else{
            return true;
        }

    }

    protected function isPositiveInteger($value,$rule,$data,$field){

        if(is_numeric($value) && is_int($value+0) && ($value + 0) > 0){
            return true;
        }

        return $field.'必须为正整数';

    }

}