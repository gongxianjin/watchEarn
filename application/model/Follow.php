<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/18
 * Time: 9:30
 */

namespace app\model;


use think\Db;
use think\Model;

class Follow extends Model
{
    //链表关注真实用户
    function joinFollowUser()
    {
        return $this->hasOne('User','c_user_id','follow_user_id')->field('nickname,headimg as user_avatar,c_user_id as user_id');
    }

    //链接关注虚拟用户
    function joinFollowDummy()
    {
        return $this->hasOne('DummyUser','id','follow_user_id')->field('nickname,user_avatar,id as user_id');
    }

    //链表  关注的人 （粉丝）
    function joinUser()
    {
        return $this->hasOne('User','c_user_id','user_id')->field('nickname,headimg as user_avatar,c_user_id as user_id');
    }
}