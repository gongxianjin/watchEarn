<?php
namespace app\index\controller;

use app\common\service\RecommendVideo;
use app\model\User;
use app\model\TempUser;
use app\model\NewVideo as Video;
use think\Controller;
use think\Model;

class Xvideo extends controller
{
    public function index(){

        $videoId = input('v_id','0');
        $to_platfrom = input("to_platfrom","");


        if(is_numeric($videoId)){
            $video = Video::find($videoId);
        }else{
            $service = new RecommendVideo();
            $postData = [
                'aliyunVideoId' => $videoId,
                'isDistribute' => ""
            ];

            $descData = $service->getAliyunVideo($postData);
            if(empty($descData)){
                exit('视频获取失败！');
            }

            $video = [];
            $temp = $descData['resultData'];
            $video['id'] = $videoId;
            $video['video_duration'] = $temp['playData']['duration'] * 1000;
            $video['video_url'] = $temp['playData']['playURL'];
            $video['video_width'] = $temp['playData']['width'];
            $video['video_height'] = $temp['playData']['height'];
            $video['video_cover'] = $temp['videoBase']['coverURL'];
            $video['title'] = $temp['videoBase']['title'];
            $video['like_count'] = $temp['videoBase']['videoId'];
            $video['uri'] = $temp['videoBase']['videoId'];
            $video['dislike_count'] = 0;
            $video['comment_count'] = 1000;
            $video['share_count'] = 1000;
        }


        if (empty($video))
            exit;

        $shareUserId = input('s_u_id',0);

        if ($shareUserId==0)
            exit;


        $user = User::where(['c_user_id'=>$shareUserId])->find();
        if(empty($user)){
            $user = TempUser::where(['c_user_id'=>$shareUserId])->find();
        }

        if (empty($user))
            exit;

        $user = $user->toArray();

        if ($user['headimg']=='')
        {
            $user['headimg'] = '/static/img/default_head.png';
        }

        $this->assign('video', is_object($video)? $video->toArray() : $video);

        $this->assign("user",$user);

        $videoList = Video::rand(10);

        foreach ($videoList as &$video)
        {
            $video = $video->toArray();

            $video['video_duration'] = $video['video_duration']/1000;
            $video['video_duration'] = floor($video['video_duration']/60)  .':'. sprintf('%02.0f',$video['video_duration']%60);
        }

        $topList = array_slice($videoList,0,5);
        $middleList = array_slice($videoList,5,2);
        $guessLikeList = array_slice($videoList,7);

        $this->assign('topList',$topList);
        $this->assign('middleList',$middleList);
        $this->assign('guessLikeList',$guessLikeList);
        $this->assign("to_platfrom",$to_platfrom);

        return  $this->fetch();
    }
    /**
     * ifranme视频播放地址
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function video(){
        $id = input("id");

        if(is_numeric($id)){
            $video = Video::find($id);
        }else{
            $service = new RecommendVideo();
            $postData = [
                'aliyunVideoId' => $id,
                'isDistribute' => ""
            ];

            $descData = $service->getAliyunVideo($postData);
            if(empty($descData)){
                exit();
            }
            $video = [];
            $temp = $descData['resultData'];
            $video['id'] = $id;
            $video['video_duration'] = $temp['playData']['duration'] * 1000;
            $video['video_url'] = $temp['playData']['playURL'];
            $video['video_width'] = $temp['playData']['width'];
            $video['video_height'] = $temp['playData']['height'];
            $video['video_cover'] = $temp['videoBase']['coverURL'];
            $video['title'] = $temp['videoBase']['title'];
            $video['like_count'] = $temp['videoBase']['videoId'];
            $video['uri'] = $temp['videoBase']['videoId'];
            $video['dislike_count'] = 0;
            $video['comment_count'] = 1000;
            $video['share_count'] = 1000;
        }
        $this->assign("video",$video);
        return $this->fetch();
    }


    public function download()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
           // die("<script type='text/javascript'>alert('亲，IOS版本还在开发中，敬请期待。')</script>");
            $this->redirect("ios");
        }

        $href = "https://play.google.com/store/apps/details?id=com.sven.huinews.international";


        $toPlatform = input('to_platfrom');
        $toPlatforms = [
            'twitter'=>'&referrer=twitter%20share_video',
            'linkedin'=>'&referrer=linkedin%20share_video',
            'facebook'=>'&referrer=facebook%20share_video',
        ];

        isset( $toPlatforms[ $toPlatform ] ) && $href .= $toPlatforms[ $toPlatform ];

        header('Location: '.$href);
        exit;
    }
    /**
     * IOS
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function ios(){
        return $this->fetch();
    }

    /**
     * 下载
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function download_by_path($path_name, $save_name){
         ob_end_clean();
         $hfile = fopen($path_name, "rb") or die("Can not find file: $path_name\n");
         Header("Content-type: application/octet-stream");
         Header("Content-Transfer-Encoding: binary");
         Header("Accept-Ranges: bytes");
         Header("Content-Length: ".filesize($path_name));
         Header("Content-Disposition: attachment; filename=\"$save_name\"");
         while (!feof($hfile)) {
            echo fread($hfile, 32768);
         }
         fclose($hfile);
    }

}