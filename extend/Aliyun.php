<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/14
 * Time: 16:17
 */

require_once 'aliyun-sdk/aliyun-php-sdk-core/Config.php';   // 假定您的源码文件和aliyun-php-sdk处于同一目录
use vod\Request\V20170321 as vod;

class Aliyun
{
    public $client;
    public $regionId = 'cn-shanghai';  // 点播服务所在的Region，国内请填cn-shanghai，不要填写别的区域

    function __construct()
    {
        $accessKeyId = \think\Env::get('aliyun.accessKeyId');
        $accessKeySecret = \think\Env::get('aliyun.accessKeySecret');

        $profile = DefaultProfile::getProfile($this->regionId, $accessKeyId, $accessKeySecret);
        $this->client =  new DefaultAcsClient($profile);
    }

    /**
     * 获取播放凭证
     *
     * @param $client
     * @param $videoId
     * @return mixed
     */
    function get_play_auth( $videoId) {
        $request = new vod\GetVideoPlayAuthRequest();
        $request->setVideoId($videoId);
        $request->setAuthInfoTimeout(3600);  // 播放凭证过期时间，默认为100秒，取值范围100~3600；注意：播放凭证用来传给播放器自动换取播放地址，凭证过期时间不是播放地址的过期时间
        $request->setAcceptFormat('JSON');
        $response = $this->client->getAcsResponse($request);
        return $response;
    }
    /**
     * 获取播放地址
     *
     * @param $client
     * @param $videoId
     * @return mixed
     */
    function get_play_info( $videoId) {
        $request = new vod\GetPlayInfoRequest();
        $request->setVideoId($videoId);
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }


    /**
     * 获取视频上传地址和凭证
     *
     * @return mixed
     */
    function create_upload_video($params) {
        $request = new vod\CreateUploadVideoRequest();
        $request->setTitle($params['title']);        // 视频标题(必填参数)
        $request->setFileName($params['fileName']); // 视频源文件名称，必须包含扩展名(必填参数)
        $request->setDescription($params['desc']);  // 视频源文件描述(可选)
//        $request->setCoverURL(); // 自定义视频封面(可选)
        $request->setTags($params['tag']); // 视频标签，多个用逗号分隔(可选)
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

    /**
     * 修改视频信息
     *
     * @param $client
     * @param $videoId
     * @return mixed
     */
    function update_video_info($videoId) {
        $request = new vod\UpdateVideoInfoRequest();
        $request->setVideoId($videoId);
        $request->setTitle('New Title');   // 更改视频标题
        $request->setDescription('New Description');    // 更改视频描述
        $request->setCoverURL('http://img.alicdn.com/tps/TB1qnJ1PVXXXXXCXXXXXXXXXXXX-700-700.png');  // 更改视频封面
        $request->setTags('tag1,tag2');    // 更改视频标签，多个用逗号分隔
        $request->setCateId(0);       // 更改视频分类(可在点播控制台·全局设置·分类管理里查看分类ID：https://vod.console.aliyun.com/#/vod/settings/category)
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

    /**
     * 删除视频信息
     *
     * @param $videoIds
     * @return mixed
     */
    function delete_videos($videoIds) {
        $request = new vod\DeleteVideoRequest();
        $request->setVideoIds($videoIds);   // 支持批量删除视频；videoIds为传入的视频ID列表，多个用逗号分隔
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

    /**
     * 获取视频源文件信息  包含下载地址
     * @param $client
     * @param $videoId
     * @return mixed
     */
    function get_mezzanine_info($videoId) {
        $request = new vod\GetMezzanineInfoRequest();
        $request->setVideoId($videoId);
        $request->setAuthTimeout(3600*5);   // 原片下载地址过期时间，单位：秒，默认为3600秒
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

    /**
     * 获取视频列表
     *
     * @param $client
     * @return mixed
     */
    function get_video_list() {
        $request = new vod\GetVideoListRequest();
        // 示例：分别取一个月前、当前时间的UTC时间作为筛选视频列表的起止时间
        $localTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $utcNow = gmdate('Y-m-d\TH:i:s\Z');
        $utcMonthAgo = gmdate('Y-m-d\TH:i:s\Z', time() - 30*86400);
        date_default_timezone_set($localTimeZone);
        $request->setStartTime($utcMonthAgo);   // 视频创建的起始时间，为UTC格式
        $request->setEndTime($utcNow);          // 视频创建的结束时间，为UTC格式
        #$request->setStatus('Uploading,Normal,Transcoding');  // 视频状态，默认获取所有状态的视频，多个用逗号分隔
        #$request->setCateId(0);               // 按分类进行筛选
        $request->setPageNo(1);
        $request->setPageSize(20);
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

    /**
     * 可删除视频流或音频流信息及存储文件，并支持批量删除；删除后当CDN缓存过期，该路流会无法播放，请谨慎操作
     *
     * @param $videoId
     * @param $jobIds
     * @return mixed|SimpleXMLElement
     */
    function delete_stream( $videoId, $jobIds) {
        $request = new vod\DeleteStreamRequest();
        $request->setVideoId($videoId);
        $request->setJobIds($jobIds);   // 媒体流转码的作业ID列表，多个用逗号分隔；JobId可通过获取播放地址接口(GetPlayInfo)获取到。
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

    /**
     * 创建视频分类，最大支持三级分类，每个分类最多支持创建100个子分类
     *  一级分类最大也是支持100个，若有更大需求请提工单联系我们
     *
     * @param $cateName
     * @param int $parentId
     * @return mixed|SimpleXMLElement
     */
    function add_category($cateName, $parentId=-1) {
        $request = new vod\AddCategoryRequest();
        $request->setCateName($cateName);   // 分类名称，不能超过64个字节，UTF8编码
        $request->setParentId($parentId);   // 父分类ID，若不填，则默认生成一级分类，根节点分类ID为-1
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

    /**
     * 删除视频分类，同时会删除其下级分类（包括二级分类和三级分类），请慎重操作
     *
     * @param $cateId
     * @return mixed|SimpleXMLElement
     */
    function delete_category($cateId) {
        $request = new vod\DeleteCategoryRequest();
        $request->setCateId($cateId);
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }

    /**
     * 获取指定的分类信息，及其子分类（即下一级分类）的列表
     *
     * @param int $cateId
     * @param int $pageNo
     * @param int $pageSize
     * @return mixed|SimpleXMLElement
     */
    function get_categories($cateId=-1, $pageNo=1, $pageSize=10) {
        $request = new vod\GetCategoriesRequest();
        $request->setCateId($cateId);   // 分类ID，默认为根节点分类ID即-1
        $request->setPageNo($pageNo);
        $request->setPageSize($pageSize);
        $request->setAcceptFormat('JSON');
        return $this->client->getAcsResponse($request);
    }
}