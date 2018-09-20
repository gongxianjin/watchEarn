<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/1
 * Time: 14:09
 */

namespace app\model;

use think\Model;

class DummyFollow extends Model
{
    function joinUser()
    {
        return $this->hasOne('User','c_user_id',"user_id");
    }

    function joinFollowUser()
    {
        return $this->hasOne('DummyUser','id',"follow_user_id");
    }
}