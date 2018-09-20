<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/14
 * Time: 17:02
 */

namespace app\common\service;
use Aliyun;
use AliyunSts;
use think\Env;

class DemandService{

    /**
     * 获取视频上传地址和凭证
     */
    function getVideoUploadInfo($params)
    {
        $aliyun = new Aliyun();
        return $aliyun->create_upload_video($params);
    }

    //获得临时用户TOKEN
    function getTokenInfo($user_id)
    {
        $aliyunSts = new AliyunSts();
        return $aliyunSts->getTokenInfo($user_id);
    }

    function getPlayInfo($videoId)
    {
        $aliyun = new Aliyun();

        return $aliyun->get_play_auth($videoId);
    }
}