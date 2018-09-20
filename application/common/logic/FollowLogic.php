<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/18
 * Time: 9:34
 */

namespace app\common\logic;
use app\model\Follow;
use app\model\User;
use app\model\DummyUser;

class FollowLogic{


    public $model;
    public $userModel;
    public $dummyUserModel;

    /**
     * FollowLogic constructor.
     * @param $type_id
     * $model[0] 关注表模型
     * $model[1] 用户表模型
     */
    function __construct(){

        $this->model = new Follow();

        $this->userModel = new User();
        $this->dummyUserModel = new DummyUser();

    }


    /**
     * 关注用户
     * $type_id 真实关注还是虚拟关注
     * $user_id 用户ID
     * $follow_user_id 被关注者ID
     * $video_id 通过某条视频进行的关注
     */
    public function followUser($data){


    }

    /**
     * 查询用户信息
     * c_user_id
     */
    public function getUserInfo($where = []){


        $userModel = $this->userModel;

        $info = $userModel->where($where)->find();

        if($info){
            return ['code'=>200,'data'=>$info];
        }else{
            return ['code'=>10001,'msg'=>'The user does not exist'];//用户不存在
        }
    }


    /**
     * 查询被关注者用户信息
     */
    public function getFollowerInfo($du_type,$id){

        $f_user = [];
        if($du_type == 1){
            $f_user = $this->userModel->where(['c_user_id' => $id])->find();
        }else if($du_type == 2){
            $f_user = $this->dummyUserModel->where(['id' => $id])->find();
        }

        if(empty($f_user)){
            return ['code'=>10001,'msg'=>'The following information is wrong'];//用户不存在
        }else{
            return ['code'=>200,'data'=>$f_user];
        }

    }

    /**
     * 查询关注信息
     */
    public function getFollowInfo($where){

        $followModel = $this->model;

        return $followModel->where($where)->find();
    }


    /**
     * 新增关注数据
     */
    public function addFollowInfo($data){

        $followModel = $this->model;

        return $followModel->insert($data);
    }


    /**
     * 修改关注数据
     */
    public function editFollowInfo($data,$where){

        $followModel = $this->model;

        return $followModel->where($where)->update($data);
    }

    //获取所有关注信息
    function getAllFollowList($condition)
    {
        $followModel = $this->model;

        return $followModel->where($condition)->select();
    }

    /**
     * 根据条件 返回相应关注数据
     *
     * @param $condition
     * @param int $page
     * @param int $pageSize
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function getByConditon($condition,$page=1,$pageSize=20,$order='update_time desc')
    {
        $followModel = $this->model;
        return $followModel->where($condition)->page($page,$pageSize)->order($order)->select();
    }
}