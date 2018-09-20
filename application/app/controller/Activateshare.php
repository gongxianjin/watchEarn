<?php
namespace app\app\controller;

use app\model\InvitationCodeShare;
use app\model\NewVideoShare;
use think\Request;
use app\app\controller\BaseController;
use app\model\Domain;
use app\common\library\Util;

class Activateshare extends BaseController
{
   
    /**
     * 分享数据获取
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    function share(){
        $key_code = input("key_code");
        if(empty($key_code)){
            $key_code = "share";
           // return out("",10001,"活动类型不能为空");
        }
        $user_id = $this->user_id;
        $invitation_code = $this->userInfo['invitation_code'];
//        $domainModel = new Domain();

        //短地址生成
//        $url = "";
//        地址生成

        $filePath = __DIR__."/mission_new/data/share.json";
        !is_file($filePath) && exit("wenjian not exists");
        $data = json_decode(file_get_contents($filePath),true);

        foreach ($data as $key => &$value) {
            //分享出去url地址
            $url = config('share_url_host').'index/openredpacket?id='.$this->user_id;
            //多图或分享图片
            if(isset($value['imgArray'])){
                //生成二维码图片
                $filename = 'user_'.$user_id.'key_code'.$key_code;
                $imgArray =Util::generateWatermarkQrCode($url, $filename);
                foreach ($value['imgArray'] as $k => &$v) {
                    $v = str_replace("share_img_host", config("share_img_host"), $v);
                }
                array_push($value['imgArray'], $imgArray);
            }

            //分享出去url地址
            $value['url'] = $url;
            $value['title'] = str_replace(["邀请码%s","URL%s"],["邀请码".$invitation_code,$url], $value['title']);
            $value['content'] = str_replace(["邀请码%s","URL%s"],["邀请码".$invitation_code,$url], $value['content']);
            //logo 图片
            $value['imgUrl'] = str_replace("share_img_host", config("share_img_host"), $value['imgUrl']);
        }
        return out($data);
       
    }

    function shareWithInvitationCode()
    {
        $filePath = __DIR__."/mission_new/data/share.json";
        !is_file($filePath) && exit("wenjian not exists");
        $data = json_decode(file_get_contents($filePath),true);
        $toPlatfrom = input("to_platfrom","old");
        foreach ($data as $key => &$value) {

            //分享出去url地址
            $value['url'] = config('share_url_host').'index/openredpacket?id='.$this->user_id.'&to_platfrom='.$toPlatfrom;
            //logo 图片
            $value['imgUrl'] = str_replace("share_img_host", config("share_img_host"), $value['imgUrl']);
        }

        (new InvitationCodeShare())->insert([
            'channel'=>$toPlatfrom,
            'user_id'=>$this->user_id,
            'ip'=>$_SERVER['REMOTE_ADDR'],
            'create_time'=>time()
        ]);


        return out($data);

    }

    /**
     * 视频分享地址获取
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function videoShare(){

        $video_id = input("video_id");
        if(empty($video_id)){
            return out([],10001);
        }
        $toPlatfrom = input("to_platfrom","old");
        //短地址生成
        $url = config("share_url_host")."/index/xvideo/index.html?v_id=$video_id&s_u_id={$this->user_id}&to_platfrom={$toPlatfrom}";

        (new NewVideoShare())->insert([
            'video_id'=>$video_id,
            'channel'=>$toPlatfrom,
            'user_id'=>$this->user_id,
            'ip'=>$_SERVER['REMOTE_ADDR'],
            'create_time'=>time()
        ]);

        return out(['url'=>$url]);
    }
   
}
