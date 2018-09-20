<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 18:10
 */
namespace app\common\service;

use app\model\User;
use app\model\DummyUser;
use think\Exception;
use app\common\logic\FollowLogic;
use app\common\logic\UserVideoLogic;

class UserService{

    //是否关注
    function getFollowStatus($userId,$otherId)
    {
        $followLogic = new FollowLogic(2);

        $condition = [
          'user_id' => $userId,
          'follow_user_id' => $otherId
        ];
        $followInfo = $followLogic->getFollowInfo($condition);

        if(!empty($followInfo) && $followInfo['status'] == 1) return true;

        return false;
    }

    //获取作品列表
    function getWorkByUser($user_id,$page,$pageSize,$order)
    {
        $videoLogic = new UserVideoLogic();
        $condition = [
            'user_id' => $user_id,
            'status' => 2,
        ];
        $temp = $videoLogic->getListByCondition($condition,$page,$pageSize,$order);
        $list = [];
        $service = new UserVideoService();
        foreach ($temp as $val)
        {
            $list[] = $service->initVideo($val);
        }
        return $list;
    }


    /**
     * 点赞  关注 修改相应数据
     * du_type 对应虚拟用户表或者真实用户表
     */
    public function editUserInfo($where,$data,$du_type){

//        if($du_type == 1){
            $model = new User();

//        }else if($du_type == 2){
//
//            $model = new DummyUser();
//        }

        return $model->where($where)->update($data);

    }
}