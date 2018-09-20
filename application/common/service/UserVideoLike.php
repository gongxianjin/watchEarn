<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/18
 * Time: 17:13
 */

namespace app\common\service;

use app\model\UserLike;
class UserVideoLike{


    protected $model;

    public function __construct(){

        $this->model = new UserLike();
    }

    /**
     * 获取点赞记录
     */
    public function userVideoTags($where = []){


        return $this->model->where($where)->find();
    }


    /**
     * 新增一条记录
     */
    public function addVideoTags($data){

        return $this->model->insert($data);
    }


    /**
     * 修改点赞记录
     */
    public function editVideoTags($where,$data){

        $model = $this->model;

        return $model->where($where)->update($data);
    }



    /**
     * 获取用户关注视频列表
     * status = 1 关注 2取消了关注的
     * c_type = 2 視頻
     * 对结果按真实>虚拟的顺序排序
     */
    public function getUserTagsVideoList($where,$page = 1,$pageSize=20){


        $data = $this->model->where($where)->where(["c_type"=>$this->model::VIDEO_TYPE])->order('du_type','ASC')->page($page,$pageSize)->select();

        if($data){
            return collection($data)->toArray();
        }
    }

}