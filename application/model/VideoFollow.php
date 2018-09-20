<?php

namespace app\model;

use think\Model;

class VideoFollow extends Model
{
    function joinUser()
    {
        return $this->hasOne('User','c_user_id',"user_id");
    }

    function joinFollowUser()
    {
        return $this->hasOne('video','user_id',"follow_user_id");
    }
}
