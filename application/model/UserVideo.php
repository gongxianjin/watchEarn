<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/17
 * Time: 14:34
 */

namespace app\model;

use think\Model;

class UserVideo extends Model
{
    protected $updateTime = false;

    function joinUser()
    {
        return $this->hasOne('User','c_user_id','user_id');
    }

}