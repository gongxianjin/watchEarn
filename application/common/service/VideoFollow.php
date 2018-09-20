<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 13:25
 */

namespace app\common\service;

use app\common\logic\VideoFollow as VideoFolloLogic;

class VideoFollow
{
    /**
     * 添加关注记录
     *
     * @param int $uid 用户ID
     * @param int $vid 视频ID
     * @param int $follow_user_id 关注视频ID
     * @return bool
     */
    function addFollow($uid,$vid,$follow_user_id)
    {
        $VideoFolloLogic = new VideoFolloLogic();

        $data = [];
        $data['user_id'] = $uid;
        $data['video_id'] = $vid;
        $data['follow_user_id'] = $follow_user_id;
        $data['create_time'] = time();
        $data['status'] = 1;
        $res = $VideoFolloLogic->add($data);
        if(empty($res)){
            return false;
        }
        return true;
    }

}