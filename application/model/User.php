<?php

namespace app\model;

use think\Cache;
use think\Model;
class User extends Model
{
    
    protected function getCreateDateAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['create_time']);
    }
    /**
     * 生成邀请码
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public static function makeInvitationCode(){
        $code = chr(rand(65,90)).generate_code(5);
        $count = self::where(['invitation_code'=>$code])->count();
        if($count>0){ 
            return self::makeInvitationCode();
        }else{
            return $code;
        }

    }
    /**
     * 获取用户信息
     * @param  模型，引用传递 net
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public static function getUserInfo($token){
        $cacheKey = 'User_Cache_Token_'.$token;
        $info = Cache::get($cacheKey);
        if(empty($info)){
            $info = self::where(['auth_token'=>$token])
                ->field("id,c_user_id,nickname,status,grade_id,sex,headimg,user_father_id,user_grandfather_id,invitation_code,birthday,lat,lng,gold_flag,total_gold_flag,frozen_gold_flag,balance,total_balance,frozen_balance,oredstatus,redcash,create_time,is_cross_read_level,paypal_mail,`unique` as openId")
                ->find();
            $info = empty($info) ? [] :  ( is_object($info) ? $info->toArray() : $info);
            Cache::set($cacheKey,$info,3600);
        }

        return $info;
    }

    /**
     * 清除token缓存
     *
     * @param $token
     */
    public function clearTokenCache($token)
    {
        $cacheKey = 'User_Cache_Token_'.$token;
        $info = Cache::get($cacheKey);
        if(!empty($info)){
            Cache::clear($cacheKey);
            Cache::set($cacheKey,'');
        }
    }

    /**
     * 获取用户信息
     * @param  模型，引用传递 net
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public static function getUserInfoById($user_id){
        $cacheKey = 'User_Cache_Token_';
        $info = Cache::get($cacheKey);
        if(empty($info)){
            $info = self::where(['c_user_id'=>$user_id])
                ->field("id,c_user_id,nickname,status,grade_id,sex,headimg,user_father_id,user_grandfather_id,invitation_code,birthday,lat,lng,gold_flag,total_gold_flag,frozen_gold_flag,balance,total_balance,frozen_balance,oredstatus,redcash,create_time,is_cross_read_level,paypal_mail,auth_token,`unique` as openId")
                ->find();
            $info = empty($info) ? [] :  ( is_object($info) ? $info->toArray() : $info);
            Cache::set($cacheKey.$info['auth_token'],$info,3600);
        }

        return $info;
    }

    public function userData()
    {
        return $this->hasOne('userData', 'user_id', 'c_user_id');
    }


    public function getCountByValue($arr = []){

        $count = self::where($arr)->count();

        return $count;
    }

    public function FindSomeItemCount($uid,$item,$map,$where,$sort,$order){

        $allList = self::where($map)
            ->whereOr(['user_grandfather_id' => $uid])
            ->where($where)
            ->order($sort, $order)
            ->select();
        $sonLists = [];
        $grandSon = [];
        foreach ($allList as $val){
            if($val['user_father_id'] == $uid){
                $sonLists[] = $val->toArray();
            }else{
                $grandSon[] = $val->toArray();
            }
        }

        $lists = [];
        foreach ($sonLists as $val){
            $isJoin = true;
            foreach ($grandSon as $v){
                if($val[$item] == $v[$item] && $v['user_father_id'] == $val['c_user_id']){
                    if($isJoin){
                        $lists[] = $val;
                        $isJoin = false;
                    }
                    $lists[] = $v;
                }
            }
        }

        return count($lists);
    }


}
