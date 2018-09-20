<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/18
 * Time: 9:33
 */

namespace app\common\service;

use app\common\logic\FollowLogic;
use app\common\logic\UserVideoLogic;
use app\model\NewVideo;

class FollowService{


    /**
     * @param $type_id
     * 根据ID 查找用户信息
     */
    public function getUserInfo($type_id,$user_id){


        $model = new FollowLogic();

        if($type_id == 1){
            $where['c_user_id'] = $user_id;
        }else if($type_id == 2){
            $where['id'] = $user_id;
        }

        $info = $model->getUserInfo($where);

        return $info;
    }


    /**
     * 查询被关注者信息
     */
    public function getFollowerInfo($du_type,$id){

        $model = new FollowLogic();

        return $model->getFollowerInfo($id,$du_type);

    }

    /**
     * 查询关注信息
     */
    public function getFollowInfo($data){

        $where = [];
        $where['user_id'] = $data['user_id'];
        $where['follow_user_id'] = $data['follower_id'];
        $where['du_type'] = $data['type_id'];
        $model = new FollowLogic();

        return $model->getFollowInfo($where);

    }

    /**
     * 新增关注数据
     */
    public function addFollowInfo($data){


        $save_data['user_id'] = $data['user_id'];
        $save_data['follow_user_id'] = $data['follower_id'];
        $save_data['status'] = 1;
        $save_data['video_id'] = $data['video_id']??0;
        $save_data['du_type'] = $data['du_type'];
        $save_data['create_time'] = time();


        $model = new FollowLogic();

        return $model->addFollowInfo($save_data);

    }


    /**
     * 修改关注数据
     */
    public function editFollowInfo($data){

        if($data){
            $status = $data['status'];

            if($status == 1){

                $edit_data['status'] = 2;
            }else if($status == 2){
                $edit_data['status'] = 1;
            }
            $edit_data['update_time'] = time();

            $model = new FollowLogic();

            return $model->editFollowInfo($edit_data,['id'=>$data['id']]);

        }
    }

    //我的关注视频列表
    function getFollowVideo($user_id,$page,$pageSize)
    {
        $followLogic = new FollowLogic();
        $condition = [
          'user_id' => $user_id,
          'status' => 1
        ];
        $followList = $followLogic->getAllFollowList($condition);
        if(empty($followList)){
            return [];
        }

        $ids ='';
        foreach ($followList as $val){
            if(empty($ids))
                $ids .= $val['follow_user_id'];
            else
                $ids .= ',' . $val['follow_user_id'];
        }

        $pageTrue = intval($pageSize / 2);

        $videoLogic = new UserVideoLogic();
        $condition = [
            'user_id' => ['in',$ids],
            'status' => 2
        ];
        $list = $videoLogic->getListByCondition($condition,$page,$pageTrue);

        $service = new UserVideoService();
        $ret = [];
        foreach ($list as $val){
            $ret[] = $service->initVideo($val);
        }

        $video = new NewVideo();
        $dum = $video->where(['du_id' => ['in',$ids]])->order('order_time desc')->page($page,$pageTrue)->select();

        foreach ($dum as $val){
            $tem = $val;
            $tem['user_id'] = $val['du_id'];
            $tem['du_type'] = 2;
            $ret[] = $tem;
        }

        return $ret;
    }

    //获取用户关注的列表
    function getUserFollowList($user_id,$page,$pageSize)
    {
        $logic = new FollowLogic();
        $condition = [
          'user_id' => $user_id,
          'status' => 1
        ];
        $order = 'update_time desc';
        $followList = $logic->getByConditon($condition,$page,$pageSize,$order);

        $ret = [];
        foreach ($followList as $val)
        {
            $temp = [];
            if($val['du_type'] == 1){
                if (!is_object($val->joinFollowUser)) continue;
                $temp = $val->joinFollowUser;
                if(empty($temp['user_avatar'])) $temp['user_avatar'] = "http://tg.199ho.com//static/img/default_head.png";
                $temp['du_type'] = 1;
            }else{
                if (!is_object($val->joinFollowDummy)) continue;
                $temp = $val->joinFollowDummy;
                if(empty($temp['user_avatar'])) $temp['user_avatar'] = "http://tg.199ho.com//static/img/default_head.png";
                $temp['du_type'] = 2;
            }
            $ret[] = $temp;
        }

        return  $ret;
    }

    //获取用户粉丝列表
    function getFansList($user_id,$du_type,$page,$pageSize)
    {
        $logic = new FollowLogic();
        $condition = [
            'follow_user_id' => $user_id,
            'status' => 1,
            'du_type' => $du_type
        ];
        $order = 'update_time desc';
        $followList = $logic->getByConditon($condition,$page,$pageSize,$order);

        $ret = [];
        foreach ($followList as $val)
        {
            $temp = [];
            if (!is_object($val->joinUser)) continue;
            $temp = $val->joinUser;
            if(empty($temp['user_avatar'])) $temp['user_avatar'] = "http://tg.199ho.com//static/img/default_head.png";
            $temp['du_type'] = 1;
            $ret[] = $temp;
        }

        return  $ret;
    }
}