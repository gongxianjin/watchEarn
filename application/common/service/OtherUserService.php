<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/29
 * Time: 13:33
 */

namespace app\common\service;

use think\Exception;

class OtherUserService
{
    private $serviceHttp = "";
    private $serviceDetail = "/open/platform/reg";
    private $defaultHead = "http://tg.199ho.com//static/img/default_head.png";
    private $otherInfo = "/user/getOtherUserInfo";
    private $followStatus = "/user/getConernStatu";
    private $serviceFollowList = "/user/mobile/userrelative/myconcernUsers";
    private $serviceLikeVideo = "/user/mobile/video/getMyFavorateVideosForTSJ";


    function __construct()
    {
        $this->serviceHttp = config('recommend_http');
    }

    //修改用户信息
    function updateUser($openId,$loginName="",$nickname='',$head='')
    {
        $url = $this->serviceHttp . $this->serviceDetail;
        $postData = ['openId' => $openId,'loginName' => $loginName,'name' => $nickname,'photo' => $head];
        $result = curl_json_post($url,$postData,true);
        try{
            $res = json_decode($result,true);

        }catch (Exception $e){
            return false;
        }
        if(!is_array($res)){
            return false;
        }

        if($res['result']){
            return true;
        }
        return false;
    }

    //同步用户信息
    function createUser($openId,$loginName,$nickname='',$head='')
    {
        $url = $this->serviceHttp . $this->serviceDetail;

        $nickname = trim($nickname);
        trace('error nickname :' .$nickname,'error');

        if(empty($nickname) || strlen($nickname) >120){
            $nickname = substr($loginName,0,strpos($loginName,'@'));
        }
        $postData = ['openId' => $openId,'loginName' => $loginName,'name' => $nickname];
        $postData['photo'] = empty($head) ? $this->defaultHead : $head;
        trace('postData :' .json_encode($postData),'error');
        $result = curl_json_post($url,$postData,true);
        trace('request Msg :' .$result,'error');
        try{
            $res = json_decode($result,true);

        }catch (Exception $e){
            return false;
        }
        if(!is_array($res)){
            return false;
        }

        if($res['result']){
            return true;
        }
        return false;
    }

    //获取他人的用户信息
    function getOtherUser($openId,$otherId)
    {
        $url = $this->serviceHttp . $this->otherInfo;
        $postData = [
            'openId' => $openId,
            'otherId' => $otherId
        ];
        $result = curl_json_post($url,$postData,true);

        try{
            $res = json_decode($result,true);

        }catch (Exception $e){
            return false;
        }
        if(!is_array($res)){
            return false;
        }

        if($res['result']){
            return $res['resultData'];
        }
        return false;
    }

    //获取关注信息
    function getFollowStatus($openId,$otherId)
    {
        $url = $this->serviceHttp . $this->followStatus;
        $postData = [
            'fromuserid' => $openId,
            'touserid' => $otherId
        ];
        $result = curl_json_post($url,$postData,true);

        try{
            $res = json_decode($result,true);

        }catch (Exception $e){
            return false;
        }
        if(!is_array($res)){
            return false;
        }

        if($res['result']){
            return $res['resultData'];
        }
        return false;
    }

    //获取关注用户列表
    function getFollowList($openId)
    {
        $url = $this->serviceHttp . $this->serviceFollowList;
        $postData = [
            'openId' => $openId
        ];
        $result = curl_json_post($url,$postData,true);

        try{
            $res = json_decode($result,true);

        }catch (Exception $e){
            return false;
        }
        if(!is_array($res)){
            return false;
        }

        if($res['result']){
            if(!empty($res['resultData'])){
                return $res['resultData'];
            }else{
                return [];
            }
        }
        return false;
    }

    //获取我喜欢的视频列表
    public function getMyLikeVideoList($openId,$start,$limit)
    {
        $url = $this->serviceHttp . $this->serviceLikeVideo;
        $postData = [
            'openId' => $openId,
            'index' => intval($start),
            'offset' => intval($limit)
        ];

        $result = curl_json_post($url,$postData,true);

        try{
            $res = json_decode($result,true);

        }catch (Exception $e){
            return false;
        }
        if(!is_array($res)){
            return false;
        }

        if($res['result']){
            if(empty($res["resultData"])){
                return [];
            }
            return $res['resultData'];
        }
        return false;

    }
}