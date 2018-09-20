<?php

namespace app\model;

use think\Cache;
use think\Model;

class TempUser extends Model
{
    protected $createTime = 'create_time';
   
    protected $autoWriteTimestamp = true;
   protected function getCreateDateAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['create_time']);
    }
   /**
     * 获取用户信息
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public static function getUserInfo($token){
        $cacheKey = 'User_Cache_Token_'.$token;
        $info = Cache::get($cacheKey);
        if(empty($info)){
            $info = self::where(['auth_token'=>$token])
                    ->field("id,c_user_id,nickname,status,grade_id,sex,headimg,user_father_id,user_grandfather_id,invitation_code,birthday,lat,lng,gold_flag,total_gold_flag,frozen_gold_flag,balance,total_balance,frozen_balance,oredstatus,redcash,create_time,is_cross_read_level")
                    ->find();
            Cache::set($cacheKey,$info,3600);
        }
        return empty($info)?[]:$info->toArray();

    }
  

}
