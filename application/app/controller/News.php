<?php
namespace app\app\controller;

use app\model\Config;
use app\model\FConfig;
use app\model\GoldProductRecord;
use \app\model\News as NewsModel;

class News extends BaseController
{

    private static $SEARCH_KEY_CODE = [
        'usual_news_read'
        ,'read_push_messages'
        ,'usual_read'
        ,'red_read'
        ,'high_quality_review_awards'
        ,'hot_search_reward'
    ];

    public function todayNews()
    {

        $rType = input('r_type',0);
        $page = input('page',1);

        if ($rType==0)
            return out([],10001,'r_type error');

        $count = 20;

        $offset = ($page-1) * $count ;

        $result = NewsModel
            ::where('group_id','eq',$rType)
            ->limit($offset,$count)
            ->order("id DESC")
            ->select();

        $result = collection($result)->toArray();

        if(empty($result)){
            $result= [
                'data'=>[]
                ,'is_has_more'=>false
            ];
            return out($result);
        }
        foreach ($result as $key => $val){
            if($val['source'] == 'buzzfeed'){
                $result[$key]['href'] = str_replace('https://www.buzzfeed.com','',$val['href']);
            }
        }

        //查询阅读次数
        $readCount = 0;
        $countS = (new GoldProductRecord())->getDailyUsage(
            $this->userInfo['c_user_id']
            ,self::$SEARCH_KEY_CODE
            ,mktime(0,0,0)
            ,mktime(24,0,0)
        );

        if (!empty($countS))
            $readCount = $countS[0]->toArray()['count'];

        $configs=[
            'gold_count'=>0
            ,'redpack_count'=>0
            ,'ad_count'=>0
        ];
        //查询配置需要数据
        $configs = FConfig::getVideolistMsg();

        //满足30次则不会在有金币，红包
        if ($readCount >= $configs['day_read_get_count'])
        {
            $configs['gold_count'] = 0;
            $configs['redpack_count'] = 0;
        }else{
            //存在,计算数量 生成的金币+红包 小于 配置数量（后续处理）


        }
        $goldCount= $configs['gold_count'];//阅读+金币
        $redpackCount = $configs['redpack_count'];//阅读红包+金币
        $adCount= $configs['ad_count']; //广告数量
        $newsStopSecond = FConfig::where('name','eq','news_stop_second')->value('value');

        foreach ($result as &$item)
        {

            $item['create_time'] = date("Y-m-d H:i:s",$item['create_time']);
            $item['is_gold'] = 0;
            $item['is_redpack'] = 0;
            $item['is_ad'] = 0;
            $item['open_browser'] = 0;
            if (rand(0,1) && $goldCount>0) {
                $item['is_gold'] = 1;
                $goldCount--;
            }elseif (rand(0,1) && $redpackCount>0) {
                $item['is_redpack'] = 1;
                $redpackCount--;
            }
            $item['ad_otherMsg']=[
                'imp'=>[],
                'clk'=>[],
            ];
            $item["ad_type"]="";
            $item['display_type'] = 1;
            if (strpos($item['cover_img'],'sz=85x64')!=false)
            {
                $item['display_type'] = 2;
                $item['cover_img'] = str_replace('&pid=News','',$item['cover_img']); // 数据转换了原图
                $item['cover_img'] = str_replace('sz=85x64','sz=540x360',$item['cover_img']); // 数据转换了大图
            }
            $item['news_stop_second'] = $newsStopSecond*1000;

        }
        $pushKey =array_flip(array_keys($result['0']));

        if($configs['ad_display_model'] == 1){
            //指定位置展示广告
            $adPosition = json_decode($configs['ad_position'],true);
            if (is_array($adPosition) && !empty($adPosition) && empty($keywords)){
                foreach ($adPosition as $k => $v) {
                    if(isset($result[$k])){
                        $pushKey['is_ad'] = 1;
                        $pushKey['ad_otherMsg']=[
                            'imp'=>[],
                            'clk'=>[],
                        ];
                        $pushKey['ad_type'] = $v;
                        array_splice($result,$k,0,[$pushKey]);
                    }
                }
            }
        }else{
            //随机展示
            foreach ($result as $l =>$d)
            {
                if (rand(0,1) && $adCount>0) {
                    $pushKey['is_ad'] = 1;
                    $pushKey['ad_otherMsg']=[
                        'imp'=>[],
                        'clk'=>[],
                    ];
                    array_splice($result,$l,0,[$pushKey]);
                    $adCount--;
                }
            }
        }
        $result= [
            'data'=>$result
            ,'is_has_more'=>count($result)>=$count?true:false
        ];
        return out($result);


    }

}
