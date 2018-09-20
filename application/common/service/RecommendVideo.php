<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/29
 * Time: 13:33
 */

namespace app\common\service;

use think\Exception;

class RecommendVideo
{
    private $serviceHttp = "";
    private $serviceList = "/user/mobile/video/getRecommendVideos2";
    private $serviceDetail = "/user/aliyun/getVideoPlayInfo";

    function __construct()
    {
        $this->serviceHttp = config('recommend_http');
    }

    function getRecommend($page,$pageSize,$openId='')
    {
        $postData = [
            'page' => $page,
            'pagesize' => $pageSize
        ];
        if(!empty($openId)){
            $postData['openId'] = $openId;
        }

        $listUrl = $this->serviceHttp . $this->serviceList;
        $result = curl_json_post($listUrl,$postData,true);

        try{
            $result = json_decode($result,true);
        }catch (Exception $e){
            return false;
        }

        //返回列表信息
        if(!$result['result'] || empty($result['resultData'])){
            return false;
        }

        $retList = [];
        $i = 0;
        foreach ($result['resultData'] as $val){
            if($i >= $pageSize){
                break;
            }
            $postData = [
                'aliyunVideoId' => $val['aliyunvideoid'],
                'isDistribute' => $val['isDistribute'],
                'videoId' => $val['id'],
            ];
            //视频详情  或者false
            $descData = $this->getAliyunVideo($postData);
            if(!$descData){
                continue;
            }

            $tmp = [];
            $temp = $descData['resultData'];
            $tmp['requestId'] = $temp['requestId'];
            $tmp['id'] = $val['aliyunvideoid'];
            $tmp['video_id'] = $val['id'];
            $tmp['otherId'] = $val['userid'];
            $tmp['video_duration'] = $temp['playData']['duration'] * 1000;
            $tmp['video_url'] = $temp['playData']['playURL'];
            $tmp['video_width'] = $temp['playData']['width'];
            $tmp['video_height'] = $temp['playData']['height'];
            $tmp['video_cover'] = $temp['videoBase']['coverURL'];
            $tmp['title'] = $temp['videoBase']['title'];
            $tmp['like_count'] = $temp['videoBase']['videoId'];
            $tmp['user_nickname'] = $val['username'];
            $tmp['user_avatar'] = $val['photo'];
            $tmp['uri'] = $temp['videoBase']['videoId'];
            $tmp['like_count'] = $val['thumbnumber'];
            $tmp['play_count'] = empty($val['playnum']) ? 0 : $val['playnum'];
            $tmp['dislike_count'] = 0;
            $tmp['comment_count'] = $val['commentnumber'];
            $tmp['share_count'] = $val['transmitnumber'];

            $retList[] = $tmp;
            $i++;
        }


        //返回播放列表
       return $retList;
    }

    //根据列表视频信息查找视频详情
    public function getAliyunVideo($postData)
    {
        //循环列表 查找视频播放地址
        $detailUrl = $this->serviceHttp . $this->serviceDetail;
        $descData = curl_json_post($detailUrl,$postData,true);

        try{
            $descData = json_decode($descData,true);
        }catch (Exception $e){
            return false;
        }

        //返回列表信息
        if(!$descData['result'] || empty($descData['resultData']) || !isset($descData['resultData']['playInfoList'][0])){
            return false;
        }
        $videoData = $descData['resultData']['playInfoList'];

        $playData = [];
        foreach ($videoData as $key => $val){
            if($val['format'] == 'mp4'){
                $playData = $val;
                break;
            }
        }

        $descData['resultData']['playData'] = $playData;
        return $descData;
    }
}