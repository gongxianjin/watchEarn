<?php

namespace app\model;

use think\Model;

class AdminUser extends Model
{
    public function authGroup()
    {
        return $this->belongsToMany('authGroup', 'auth_group_access', 'group_id', 'aid');
    }

    protected function getCreateDateAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['create_time']);
    }
}
