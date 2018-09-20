<?php

namespace app\model;

use think\Model;

class AuthGroup extends Model
{
    protected function getCreateDateAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['create_time']);
    }
}
