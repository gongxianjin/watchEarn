<?php
namespace app\model;

use think\Model;

class UserLike extends Model
{

    const NEWS_TYPE = 1;
    const VIDEO_TYPE = 2;

    function joinUser()
    {
        return $this->hasOne('User','c_user_id','c_user_id');
    }

    function joinVideo()
    {
        return $this->hasOne('NewVideo','id','a_v_id');
    }
}
