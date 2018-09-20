<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 15:35
 */

namespace app\script\command;

use app\common\model\Redis;
use app\model\NewVideo;
use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;

class Tiktok extends Command
{
    private $listKey = 'aweme_list';

    private $channel = 'musical.ly';

    private $veeuUrl = 'https://api.tiktokv.com/aweme/v1/feed/?type=0&min_cursor=0&count=5&iid=31622828589&device_id=40545321430&ac=wifi&channel=googleplay&aid=1180&app_name=trill&version_code=110&version_name=1.1.0&device_platform=android&ssmix=a&device_type=Redmi+4X&device_brand=Xiaomi&os_api=25&os_version=7.1.2&uuid=865431036484930&openudid=e18751bf13ed007f&manifest_version_code=110&resolution=720*1280&dpi=320&update_version_code=1100&ts=1534937807&as=a1355457af5cabbafd&cp=43cab558fdd773ade1&app_language=en&language=en&region=US&sys_region=CN&carrier_region=cn&build_number=1.1.0';

    protected function configure()
    {
        //设置执行名称
        $this->setName('tiktok')
            ->setDescription('Run all the time');
    }

    protected function execute(Input $input, Output $output)
    {
        $video = new NewVideo();
        while (true) {
            $id = rand(2, 11);
            $urlInfo = Db::table('douyin_cather')->where(['id' => $id])->find();
            $url = $urlInfo['url'];

            $result = curl_get_https($url);
            $result = json_decode($result, true);


            $lists = empty($result[$this->listKey]) ? [] : $result[$this->listKey];
            $insertData = [];
            if (!empty($lists)) {
                foreach ($lists as $val) {
                    $data = [];

                    $like_count = $val['statistics']['digg_count'];   //点赞
                    $comment_count = $val['statistics']['comment_count'];   //评论
                    $share_count = $val['statistics']['share_count'];   //分享
                    $play_count = $val['statistics']['play_count'];   //播放
                    $duration = empty($val['video']['duration']) ? 0 : $val['video']['duration'];  //视频时长

                    //检测中文  如有中文 不采集
                    $videoUserName = $val['author']['nickname'];
                    $title = empty($val['desc']) ? '' : $val['desc'];
                    if ($this->checkChinese($videoUserName . $title) != -1) {
                        continue;
                    }

                    if ($like_count >= 0 && $duration > 8000) {
                        $uri = str_replace($val['video']['ratio'], '', $val['video']['play_addr']['uri']);
                        //判断是否 已经存在
                        $isExist = $video->field('id')->where(['uri' => $uri, 'channel' => $this->channel])->find();
                        if ($isExist) {
                            continue;
                        }
                        try {
                            $data['title'] = trim($title);
                            $data['category'] = $this->channel;
                            $data['video_url'] = $val['video']['play_addr']['url_list'][0];
                            $data['video_uni'] = $uri;
                            $data['video_duration'] = $val['video']['duration'];
                            $data['video_cover'] = $val['video']['cover']['url_list'][0];
                            $data['video_width'] = $val['video']['width'];
                            $data['video_height'] = $val['video']['height'];
                            $data['like_count'] = $like_count;
                            $data['dislike_count'] = 0;
                            $data['comment_count'] = $comment_count;
                            $data['share_count'] = $share_count;
                            $data['play_count'] = $play_count;
                            $data['group_id'] = 0;
                            $data['user_id'] = $val['author']['uid'];
                            $data['user_nickname'] = trim($videoUserName);
                            $data['user_avatar'] = $val['author']['avatar_larger']['url_list'][0];
                            $data['uri'] = $uri;
                            $data['is_handler_comment'] = 0;
                            $data['create_time'] = time();
                            $data['collect_count'] = 0;
                            $data['channel'] = $this->channel;
                            $data['visit_count'] = 0;
                            $data['status'] = 1;
                            $data['dis_time'] = '';
                            $data['order_time'] = time();
                            $data['r_type'] = 1;
                            $data['insert_key'] = 'wl';
                        } catch (Exception $e) {
                            continue;
                        }
                        if(empty($data['user_nickname'])){
                            continue;
                        }
                        $insertData[] = $data;
                    }
                }

                if (!empty($insertData)) {
                    try{
                        $num = count($insertData);
                        $video->saveAll($insertData);
                        $output->writeln('success! + ' . $num . ' --------- ' . date(' Y-m-d H:i:s'));
                    }catch (Exception $e){
                        $output->writeln('insert error!');
                    }
                } else {
                    $output->writeln('no one video!');
                }
            }

            //间隔随机时间
            $sleep = 5;
            sleep($sleep);
        }
    }

    //检测中文
    function checkChinese($string)
    {
        if (preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $string) === 1) {
            //全是中文
            return 1;
        } elseif (preg_match('/[\x{4e00}-\x{9fa5}]/u', $string) === 1) {
            //包含中文
            return 0;
        }
        return -1;
    }
}