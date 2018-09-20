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

class VeeuVideo extends Command
{
    private $listKey = 'doc_list';

    private $keyName = [
        'video_url' => '视频地址',
        'user_id' => '发布用户 veeu ID ',
        'locale' => '服务器地区',   // en_US  美国
        'like_count' => '点赞',
        'duration' => '视频时长（秒）',
        'publisher_profile_picture_url' => '头像地址',
        'publisher_name' => '发行者名称',
        'followed_by_ad' => '是否为广告',
        'cover_img_urls' => '缩略图（数组，可能多张）',
        'title' => '标题',
        'source' => '得分',
        'share_count' => '分享次数',
        'video_ratio' => '分辨率 数组 [宽 ， 高]',
        'tags' => '关键字 [关键字1，关键字2]',
        'content_url' => '视频地址',   //与第一个地址暂时无区别  不知其具体用法
        'video_type' => '视频分类',
        'content_type' => '内容分类',
        'rec_reason' => '未知  （对象）',
        'inlinks' => '未知  （数组）',
        'publish_ts' => '出版商号',
        'doc_id' => '视频唯一吗 （id号）',
    ];

    private $tagArr = ["comedy","fashion","interest","dance","baby","relationship","abs","step","society","life","food","mystery","entertainment","animal","beauty","cherry_matrix","travel","technology","cartoon","hifit_no_copyright","manfit","game","fitness","military","health","magic","horoscope_matrix","talent","hifit_matrix","howto","girl","vehicle"];
    /* “喜剧”，“时尚”，“舞蹈”，“婴儿”，“社会”，“生活”，“食物”，“神秘”，“娱乐”，“动物”，“美女”，“旅游”，“技术”，“卡通”，“HIFITITNO版权”，“MaFIT”，“游戏”，“健身”，“军事”，“健康”，“魔术”，“占星术矩阵”，“天才”，“HIFITIZ”。矩阵“，”HOTO“，”女孩“，”车辆“
    */
    private $t_type_key = [
        '1' => ["comedy","interest","fashion","entertainment","beauty","talent","vehicle","dance","travel"],
        '2' => ["mystery","cherry_matrix","technology","manfit","game","fitness","military","magic","horoscope_matrix"]
    ];

    private $veeuHost = 'https://www.veeuapp.com';
    private $veeuUrl = '/v1.0/docs?access_token=97b36465-a23a-4b3c-8583-9764270e3e8f&page_type=foryou&start=0&count=12&session_id=53b0ecec-5d03-44a8-9b67-d2282d2447e8&id=not_login&locale=zh_CN&doc_id=14103961731336271984';
    private $Etag = '8917d0561dd5b6f363262da9dd4827f237987eb8';

    private $channel = 'veeu';

    protected function configure()
    {
        //设置执行名称
        $this->setName('veeu')
            ->setDescription('Run all the time');
    }

    protected function execute(Input $input, Output $output)
    {
        $video = new NewVideo();
        $tagKey = 'Video_tag';
        $redis = Cache::init();

        while (true) {
            $result = curl_get_https($this->veeuHost . $this->veeuUrl, ['Etag' => $this->Etag]);
            $result = json_decode($result, true);

            $tagArr = $redis->get($tagKey) ? json_decode($redis->get($tagKey),true) : [];

            $lists = $result[$this->listKey];
            $insertData = [];
            if(!empty($lists)) {
                foreach ($lists as $val) {
                    $data = [];
                    if (($val['like_count'] >= 80 || $val['share_count'] >= 20 || $val['comment_count'] >= 50) && $val['duration'] > 15) {
                        //判断是否 已经存在
                        $isExist = $video->field('id')->where(['uri' => $val['doc_id'],'channel' =>$this->channel])->find();
                        if($isExist){
                            continue;
                        }

                        $data['title'] = removeEmoji($val['title']);
                        $data['category'] = $val['video_type'];
                        $data['video_url'] = $val['video_url'];
                        $data['video_uni'] = $val['doc_id'];
                        $data['video_duration'] = $val['duration'] * 1000;
                        $data['video_cover'] = $val['cover_img_urls'][0];
                        $data['video_width'] = $val['video_ratio'][0];
                        $data['video_height'] = $val['video_ratio'][1];
                        $data['like_count'] = $val['like_count'];
                        $data['dislike_count'] = 0;
                        $data['comment_count'] = $val['comment_count'];
                        $data['share_count'] = $val['share_count'];
                        $data['play_count'] = 0;
                        $data['group_id'] = 0;
                        $data['user_id'] = $val['user_id'];
                        $data['user_id'] = $val['user_id'];
                        $data['user_nickname'] = $val['publisher_name'];
                        $data['user_avatar'] = $val['publisher_profile_picture_url'];
                        $data['uri'] = $val['doc_id'];
                        $data['is_handler_comment'] = 0;
                        $data['create_time'] = time();
                        $data['collect_count'] = 0;
                        $data['channel'] = $this->channel;
                        $data['visit_count'] = 0;
                        $data['status'] = 1;
                        $data['dis_time'] = '';
                        $data['order_time'] = time();
                        $data['r_type'] = $this->getTtype($val);
                        $insertData[] = $data;
                        if(!empty($val['tags'])){
                            foreach ($val['tags'] as $v){
                                if(!in_array($v,$tagArr)){
                                    $tagArr[] = $v;
                                }
                            }
                        }

                    }
                }

                if(!empty($tagArr)){
                    $redis->set($tagKey,json_encode($tagArr));
                }

                $ids = [];
                if (!empty($insertData)) {
                    $ids = $video->saveAll($insertData);
                    trace('add ids :' . json_encode($ids), 'debug');
//                    $output->writeln(json_encode($ids));
                    $output->writeln('success!');
                }else{
                    $output->writeln('no one video!');
                }
            }

            //间隔随机时间
            $sleep = rand(10,20);
            sleep($sleep);
        }
    }

    /**
     * 设置t_type
     * @param array $item
     * @return int|string
     */
    function getTtype(array  $item)
    {
        $tagArr = $this->t_type_key;
        $t_type = 3;
        foreach ($tagArr as $key => $val){
            foreach ($item['tags'] as $v){
                if(in_array($v,$val)){
                    $t_type = $key;
                    return $t_type;
                }
            }
        }

        return $t_type;
    }
}