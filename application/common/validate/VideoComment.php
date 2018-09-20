<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 14:15
 */

namespace app\common\validate;


use app\common\exception\ParameterException;

class VideoComment extends BaseValidate
{

    protected $rule = [
        'v_id' => 'require',
        'content' => 'require|checkContent'
    ];


    protected function  checkContent($value){

        if (isset($value))
            $value = trim($value);
        if ($value === '' || mb_strlen($value)>=256)
            throw new ParameterException([
                'msg' => 'comment content is error',
            ]);
        return true;

    }

}