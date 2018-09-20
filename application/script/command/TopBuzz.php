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

class TopBuzz extends Command
{
    private $listKey = 'items';

    private $channel = 'topbuzz';

    private $veeuUrl = 'https://i16-tb.isnssdk.com/api/663/stream?category=13&category_parameter=&tab=Video&count=20&min_behot_time=1.536228156999E9&sign=b5db36de19469d61f6b6942731941588f721d652&timestamp=1536228280&logo=topbuzz&gender=0&weather_push=0&youtube=0&manifest_version_code=663&app_version=6.6.3&iid=6588346698093938437&gaid=c6be804d-7148-4ebc-8bff-cbaa41ead89c&original_channel=gp&channel=gp&fp=FrT_LWmbJYTqFYQrJlU1LMTrFlXq&device_type=Mi+Note+3&language=en&resolution=1920*1080&openudid=2cd52112ae4ae5df&update_version_code=6630&sys_language=en&sys_region=us&os_api=25&tz_name=Asia%2FShanghai&tz_offset=28800&dpi=440&brand=Xiaomi&ac=WIFI&device_id=6588345859187049990&os=android&os_version=7.1.1&version_code=663&device_brand=Xiaomi&device_platform=android&sim_region=cn&region=us&aid=1184';

    protected function configure()
    {
        //设置执行名称
        $this->setName('topbuzz')
            ->setDescription('Run all the time');
    }

    protected function execute(Input $input, Output $output)
    {
        $video = new NewVideo();
        while (true) {
//            $id = rand(2, 11);
//            $urlInfo = Db::table('douyin_cather')->where(['id' => $id])->find();
//            $url = $urlInfo['url'];
            $url = $this->veeuUrl;
            $output->writeln("start time :" . time());
            $resultJson = curl_get_https($url);
            $output->writeln("end time :" . time());

            $result = json_decode($resultJson, true);

            $lists = empty($result['data'][$this->listKey]) ? [] : $result['data'][$this->listKey];
            $insertData = [];
            if (!empty($lists)) {
                foreach ($lists as $val) {
                    $data = [];
                    $like_count = $val['like_count'];   //点赞
                    $comment_count = $val['comment_count'];   //评论
                    $share_count = $val['share_count'];   //分享
                    $play_count = 0;   //播放
                    $duration = empty($val['video']['duration']) ? 0 : $val['video']['duration'] * 1000;  //视频时长

                    //检测中文  如有中文 不采集
                    $videoUserName = $val['author']['name'];
                    $title = empty($val['title']) ? '' : $val['title'];
                    if ($this->checkChinese($videoUserName . $title) != -1) {
                        continue;
                    }
                    $width = $val['video']['width'];
                    $height = $val['video']['height'];
                    $bili = ($width * 0.1) / ($height * 0.1);
                    if($bili < 1.2){
                        continue;
                    }
                    $output->writeln('比例:' . $bili ." 时长：".$duration);

                    if ($like_count >= 0 && $duration > 8000) {
                        $uri = $val['item_id'];
                        //判断是否 已经存在
                        $isExist = $video->field('id')->where(['uri' => $uri, 'channel' => $this->channel])->find();
                        if ($isExist) {
                            continue;
                        }
                        try {
                            $data['title'] = trim($title);
                            $data['category'] = $this->channel;
                            $data['video_url'] = $val['url'];
                            $data['video_uni'] = $uri;
                            $data['video_duration'] = $duration;
                            $data['video_cover'] = $val['large_image']['url_list'][0]['url'];
                            $data['video_width'] = $val['video']['width'];
                            $data['video_height'] = $val['video']['height'];
                            $data['like_count'] = $like_count;
                            $data['dislike_count'] = 0;
                            $data['comment_count'] = $comment_count;
                            $data['share_count'] = $share_count;
                            $data['play_count'] = $play_count;
                            $data['group_id'] = 0;
                            $data['user_id'] = $val['author']['author_id'];
                            $data['user_nickname'] = trim($videoUserName);
                            $data['user_avatar'] = $val['author']['avatar']['uri'];
                            $data['uri'] = $uri;
                            $data['is_handler_comment'] = 0;
                            $data['create_time'] = time();
                            $data['collect_count'] = 0;
                            $data['channel'] = $this->channel;
                            $data['visit_count'] = 0;
                            $data['status'] = 1;
                            $data['dis_time'] = '';
                            $data['order_time'] = time();
                            $data['r_type'] = 2;
                            $data['insert_key'] = 'test';
                        } catch (Exception $e) {
                            trace('jsonError:',$resultJson,'error');
                            continue;
                        }
                        if(empty($data['user_nickname'])){
                            continue;
                        }
                        trace("insert :".json_encode($data),'error');
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