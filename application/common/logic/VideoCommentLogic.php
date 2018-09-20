<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 13:25
 */

namespace app\common\logic;

use app\model\VideoComment;

class VideoCommentLogic
{
    public $model;

    //初始化评论表
    function __construct($videoId)
    {
        $this->model = VideoComment::instance($videoId);
    }


    /**
     * 根据条件查找视频评论列表
     *
     * @param $condition  查找条件
     * @param int $page   页码
     * @param int $pageSize 分页条数
     * @param string $sort   排序规则
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function getCommentByCondition($condition,$page=1,$pageSize=20,$sort='create_time desc',$user_id)
    {
//        return $this->model->where($condition)->order($sort)->paginate($pageSize,false,['page'=>$page]);
        return $this->model->alias('a')->where($condition)->order($sort)->join('User u','a.user_id = u.c_user_id','LEFT')->field('a.id,a.video_id,a.user_id,u.headimg as avatar,u.nickname,a.content,a.like_count,a.create_time')->paginate($pageSize,false,['page'=>$page]);

    }

    /**
     * 添加评论
     *
     * @param $data
     * @return int|string
     */
    function add($data)
    {
        return $this->model->insert($data);
    }


    /**
     * 添加评论
     *
     * @param $data
     * @return int|string
     */
    function updateComment($where,$data)
    {
        return $this->model->where($where)->update($data);
    }


    /**
     * 根据条件查找视频评论列表
     *
     * @param $condition  查找条件
     * @param string $sort   排序规则
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function getAllCommentsByCondition($condition,$sort='create_time desc')
    {
        return $this->model->alias('a')->where($condition)->order($sort)->join('User u','a.user_id = u.c_user_id','LEFT')->field('a.*,u.nickname,u.headimg')->select();
    }

    /**
     * 根据条件查找视频评论
     *
     * @param $condition  查找条件
     * @param string $sort   排序规则
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function getCommentsByCondition($condition)
    {
        return $this->model->where($condition)->find();
    }


}