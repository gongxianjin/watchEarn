<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15
 * Time: 16:36
 */

namespace app\common\validate;


class PagingParameter extends BaseValidate
{

    protected $rule = [
        'page' => 'isPositiveInteger',
        'size' => 'isPositiveInteger'
    ];

    protected $message = [
        'page' => 'page must is interger',
        'size' => 'page must is interger'
    ];

}