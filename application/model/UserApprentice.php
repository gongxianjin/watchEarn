<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-23
 * Time: 下午2:01
 */

namespace app\model;

use think\Model;

class UserApprentice extends Model
{
    protected function getCreateDateAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['create_time']);
    }

    public function user()
    {
        return $this->belongsTo('user', 'apprentice_user_id', 'c_user_id')->field('c_user_id,telphone,nickname,headimg,create_time');
    }
}