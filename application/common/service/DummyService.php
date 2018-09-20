<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19
 * Time: 15:59
 */
namespace app\common\service;

class DummyService
{

    function DummyFollow($other_id,$page,$pageSize)
    {
//虚假用户生成关注数量
        $likeUserKey = "DummyUserFollow_"  . $other_id;
        $followNum = cache($likeUserKey);
        if(empty($followNum)){
            $followNum = rand(1,20);
            cache($likeUserKey,$followNum);
        }

        //虚假用户生成关注数量
        $likeUserListKey = "DummyUserFollowList_"  . $other_id;

        $list = cache($likeUserListKey);
        if(empty($list)){
            $userModel = new \app\model\User();
            $userList = $userModel->where(['status' => 1,'is_cross_read_level' => 0,'headimg' => ['neq','']])->order('rand()')->limit($followNum)->select();
            $list = [];
            foreach ($userList as $val){
                $temp = [];
                $temp['user_id'] = $val->c_user_id;
                $temp['nickname'] = $val->nickname;
                $temp['user_avatar'] = $val->headimg;
                $temp['du_type'] = 1;
                $list[] = $temp;
            }

            cache($likeUserListKey,$list);
        }
        $startKey = ($page - 1) * $pageSize;
        $tempNum = $pageSize;
        $retList =  [];
        foreach ($list as $key => $item)
        {
            if($tempNum <=0){
                break;
            }
            if($key >= $startKey){
                $retList[] = $item;
                $tempNum--;
            }
        }
        return $retList;
    }
}