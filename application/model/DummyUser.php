<?php

namespace app\model;

use think\Cache;
use think\Model;
class DummyUser extends Model
{
    
    protected function getCreateDateAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['create_time']);
    }

}
