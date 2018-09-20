<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/14
 * Time: 18:18
 */

namespace app\common\validate;


class IDMustBePositiveInt extends BaseValidate
{

    protected $rule = [
        'id' =>'require|isPositiveInteger'
    ];



}