<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/14
 * Time: 17:21
 */
namespace app\app\controller;

use app\common\logic\TagLogic;
use app\common\logic\UserVideoLogic;
use app\common\service\DemandService;
use app\common\service\UserService;
use app\common\service\UserVideoService;
use app\model\Tag;
use think\Exception;

class Demand extends BaseController
{
    function getUploadInfo()
    {
        $data['title'] = input('post.title','');
        $data['fileName'] = input('post.fileName','');
        $data['desc'] = '';
        $data['tag'] = '';

        $service = new DemandService();

        $ret = $service->getVideoUploadInfo($data);

        return out($ret,200,'success');
    }

    //获取用户上传临时阿里云凭证
    function getUserToken()
    {
        $user_id = $this->user_id;
        $service = new DemandService();
        $expart = 3000;
        $cacheKey = "CacheToken_".$user_id;
        $tokenInfo = cache($cacheKey);
        if(empty($tokenInfo)){
            $ret = $service->getTokenInfo($user_id);
            try{
                //对象转换数组
                $ret = json_decode(json_encode($ret),true);
                $tokenInfo['RequestId'] = $ret['RequestId'];
                $tokenInfo['AccessKeyId'] = $ret['Credentials']['AccessKeyId'];
                $tokenInfo['AccessKeySecret'] = $ret['Credentials']['AccessKeySecret'];
                $tokenInfo['Expiration'] = $ret['Credentials']['Expiration'];
                $tokenInfo['SecurityToken'] = $ret['Credentials']['SecurityToken'];
                cache($cacheKey,$tokenInfo,['expire' => $expart]);
            }catch (Exception $e){
                $tokenInfo = [];
                cache($cacheKey,$tokenInfo,['expire' => 0]);
            }
        }

        if(empty($tokenInfo)){
            return out($tokenInfo,10002,'service success');
        }
        return out($tokenInfo,200,'success');
    }

    //上传阿里云视频接口
    function updateVideoId()
    {
        $data['user_id'] = $this->user_id;
        //阿里云ID
        $data['aliyun_video_id'] = input('post.videoId');
        if(empty($data['aliyun_video_id'])){
            return out([],10002,'Video ID Is Error!');
        }
        //封面图
        $data['cover_img'] = input('post.coverImg');
        if(empty($data['cover_img'])){
            return out([],10002,'Wrong Cover Picture!');
        }

        //标题
        $data['title'] = input('post.title');
//        if(empty($data['title'])){
//            return out([],10002,'Please Input Video Title!');
//        }

        //时长
        $data['duration'] = input('post.duration');
        if(empty($data['duration']) || $data['duration'] < 5000){
            return out([],10002,'Video duration is too short.');
        }
        //宽
        $data['video_width'] = input('post.width');
        if(empty($data['video_width'])){
            return out([],10002,'Video message is error.');
        }
        //高
        $data['video_height'] = input('post.height');
        if(empty($data['video_height'])){
            return out([],10002,'Video message is error.');
        }

        $data['r_type'] = 1;
        if($data['video_width'] > $data['video_height']){
            $data['r_type'] = 2;
        }

        //视频上传经纬度
        $lat = $this->getHeader('lat','');
        $lng = $this->getHeader('lng','');
        if(!empty($lat) && empty($lng)){
            $data['lat'] = $lat;
            $data['lng'] = $lng;
        }

        $country = $this->getHeader('country','');
        if(!empty($country)){
            $data['country'] = $country;
        }

        //标签
        $tag = input('post.tag');
        $data['tag'] = '';
        if(!empty($tag)){
            $data['tag'] = $tag;
            $tagModel = new Tag();
            $tagModel->where(['tag' => ['in',$data['tag']]])->update(['use_count' => ['exp','use_count + 1'] ]);
        }
        $data['create_time'] = time();
        $data['order_time'] = time();

        $service = new UserVideoService();
        $res = $service->addUserVideo($data);
        if($res['code'] == 0){
            return out([],200,'success');
        }
        return out([],$res['code'],$res['msg']);
    }

    //标签管理
    function tag()
    {
        $la = $this->language;

        $logic = new TagLogic();
        $condition = [
            'status' => 1
        ];
        $order = 'sort desc';
        $tags = $logic->getByCondition($condition,$order);
        $ret = [];
        foreach ($tags as $val){
            $temp['tag'] = $val['tag'];
            $tag_names = json_decode($val['tag_name'],true);

            $temp['tag_name'] = $tag_names[$la] ?? reset($tag_names);
            $ret[] = $temp;
        }

        return out($ret,200,'success');
    }
}