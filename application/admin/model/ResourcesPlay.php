<?php

namespace app\admin\model;

use think\Model;

class ResourcesPlay extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = false;
       // 关闭自动写入update_time字段
    protected $updateTime = false;

    public function getTitleAttr($value, $data)
    {
        return __($value);
    }
}
