<?php

namespace app\admin\model;

use think\Model;

class Resources extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
       // 关闭自动写入update_time字段
    protected $updateTime = false;

    public function getTitleAttr($value, $data)
    {
        return __($value);
    }
    /**
     * 作用
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function Platform()
    {
        return $this->belongsTo('Platform', 'p_type')->setEagerlyType(0);
    }
}
