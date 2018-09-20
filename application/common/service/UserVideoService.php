<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/17
 * Time: 15:19
 */

namespace app\common\service;

use app\common\logic\UserVideoLogic;

use app\model\Video;
use app\model\UserVideo;
use think\Exception;
use app\model\NewVideo;

class UserVideoService
{
    //用户上传视频
    function addUserVideo($data)
    {
        $logic = new UserVideoLogic();
        $unique['aliyun_video_id'] = $data['aliyun_video_id'];
        //查询是否唯一
        $info = $logic->findByCondition($unique);

        if($info === false){
            return ['code' => 10003,'msg' => 'service connect error'];
        }

        if(!empty($info)){
            return ['code' => 10004,'msg' => 'Video has been uploaded successfully.'];
        }

        $re = $logic->add($data);
        if($re){
            return ['code' => 0,'msg' => 'Video has been uploaded successfully.'];
        }

        return ['code' => 10005,'msg' => 'Video upload failure.'];
    }

    //获取推荐视频
    function getRecommend($type,$page,$pageSize,$order)
    {
        $logic = new UserVideoLogic();
        $condition = [
            'r_type' => $type,
            'status' => 2
        ];
        $temp = $logic->getListByCondition($condition,$page,$pageSize,$order);
        $list = [];

        foreach ($temp as $val)
        {
            $list[] = $this->initVideo($val);
        }
        return $list;
    }

    /**
     * 获取视频信息
     * du_type 虚拟视频或者真实视频
     */
    public function getVideoInfo($du_type,$video_id){

        if($du_type == 1){
            $model = new UserVideo();
            $where['aliyun_video_id'] = $video_id;
        }else if($du_type == 2){
            $model = new NewVideo();
            $where['id'] = $video_id;
        }

        return $model->where($where)->find();
    }


    /**
     * 修改视频like_count
     * 视频id
     * du_type 视频源
     */
    public function editVideoLikeCount($du_type,$video_id,$data){

        if($du_type == 1){
            $model = new UserVideo();
            $where['aliyun_video_id'] = $video_id;
        }else if($du_type == 2){
            $model = new NewVideo();
            $where['id'] = $video_id;
        }

        return $model::update($data,$where);

    }

    function getVideoUrl($videoId)
    {
        $logic = new UserVideoLogic();
        $info = $logic->findByCondition(['aliyun_video_id' => $videoId]);
        if(empty($info)){
            return ['code' => 10001,'video not find','data' => []];
        }

        $tmp = $this->initVideo($info);

        $aliyun = new \Aliyun();
        $playInfo = $aliyun->get_play_info($videoId);
        try{
            $playInfo = json_decode(json_encode($playInfo),true);
            $video_url = "";

            foreach ($playInfo['PlayInfoList']['PlayInfo'] as $val){
                if($video_url) break;
                if($val['Format'] == 'mp4'){
                    $video_url = $val['PlayURL'];
                }
            }
            $tmp['video_url'] = $video_url;
        }catch (Exception $e){
            return ['code' => 10003,'msg' => 'video message error','data' => []];
        }
        if($video_url){
            return ['code' => 0,'msg' => 'success','data' => $tmp];
        }

        return ['code' => 10004,'msg' => 'video message error!','data' => []];

    }

    //格式化
    function initVideo($val)
    {
        $tmp = [];
        $tmp['id'] = $val['aliyun_video_id'];
        $tmp['title'] = $val['title'];
        $tmp['aliyun_id'] = $val['aliyun_video_id'];
        $tmp['video_duration'] = $val['duration'];
        $tmp['video_cover'] = $val['cover_img'];
        $tmp['video_height'] = $val['video_height'];
        $tmp['video_width'] = $val['video_width'];
        $tmp['like_count'] = $val['like_count'];
        $tmp['comment_count'] = $val['comment_count'];
        $tmp['share_count'] = $val['share_count'];
        $temp['order_time'] = $val['order_time'];
        $tmp['play_count'] = $val['play_count'];
        $tmp['r_type'] = $val['r_type'];
        $tmp['user_id'] = $val['user_id'];
        $tmp['user_nickname'] = $val->joinUser->nickname;
        $tmp['user_avatar'] = empty($val->joinUser->user_avatar) ? "http://tg.199ho.com//static/img/default_head.png" : $val->joinUser->user_avatar;
        $tmp['du_type'] = 1;

        return $tmp;
    }
}