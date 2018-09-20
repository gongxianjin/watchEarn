<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use app\app\library\UserTackRecord;
use app\model\GoldProductRecord;
use app\model\GoldRun;
use app\model\UserTaskRecord;



class ShareFriendPrentice extends BaseController implements MissionInterface
{

    private static $SEARCH_KEY_CODES = [
        'share_friend_prentice_read' //朋友圈阅读分享
    ];

    /**
     * 间隔时间
     */
    const HOURS = 4;

    /**
     * @var GoldRun
     */
    protected $goldRun = null;

    function _initGoldRun(&$goldRun)
    {
        $this->goldRun = $goldRun;
    }

    /**
     * @return array|mixed
     */
    function info()
    {
        $goldRun = &$this->goldRun;
        $userInfo = &$this->userInfo;


        $goldProductRecord = new GoldProductRecord();

        //今天起始时间
        $todayBeginTime = mktime(0,0,0);
        //今天结束时间
        $todayEndTime = mktime(23,59,59);

        //本周起始时间星期一
        $weekBeginTime = mktime(0,0,0) - (date('N')-1) * 3600*24;
        //现在时间
        $nowTime = time();

        //查询今日个数
        $count = $goldProductRecord
            ->where('type_key','eq',$goldRun['key_code'])
            ->where('create_time','between',[$todayBeginTime,$todayEndTime])
            ->where('user_id','eq',$userInfo['c_user_id'])
            ->count();
        //本周数量
        $dailyData = $goldProductRecord
            ->getDailyUsage($userInfo['c_user_id'],self::$SEARCH_KEY_CODES,$weekBeginTime,$nowTime);

        //最后一次
        $last = $goldProductRecord
            ->where('type_key','eq',$goldRun['key_code'])
            ->where('create_time','between',[$todayBeginTime,$todayEndTime])
            ->where('user_id','eq',$userInfo['c_user_id'])
            ->value('create_time');

        $time = time();
        $last == false && $last = $time-3600*self::HOURS;

        $dailyDataCount = 0;
        $reward = 0;
        foreach ($dailyData as &$daily)
        {
            $reward +=$daily['gold'];
            $dailyDataCount +=$daily['count'];
        }

        return out([
            'left_time'=>$goldRun['num']-$count
            ,'friend_read_count'=>$dailyDataCount
            ,'friend_read_reward'=>$reward
            ,'next_second'=>3600*self::HOURS - ($time-$last)
        ]);
    }

    /**
     *
     * 分享朋友圈收图
     * @return array|mixed
     * @throws
     */
    function handler()
    {

        $goldRun = &$this->goldRun;

        // 模拟用户数据
        $userInfo = &$this->userInfo;

        $goldProductRecord = new GoldProductRecord();

        $todayBeginTime = mktime(0,0,0);
        $todayEndTime = mktime(23,59,59);

        //查询今日个数
        $count = $goldProductRecord
            ->where('type_key','eq',$goldRun['key_code'])
            ->where('create_time','between',[$todayBeginTime,$todayEndTime])
            ->where('user_id','eq',$userInfo['c_user_id'])
            ->order("id DESC")
            ->count();

        if ($count>=5)
        {
            return out([
                'left_time'=>$goldRun['num']-$count-1
                ,'next_second'=> 0
                ,'is_add_gold'=> 0
                ,'gold'=> 0
            ],'200','请明天重试');
        }

        $last = $goldProductRecord
            ->where('type_key','eq',$goldRun['key_code'])
            ->where('create_time','between',[$todayBeginTime,$todayEndTime])
            ->where('user_id','eq',$userInfo['c_user_id'])
            ->order("id DESC")
            ->find();

        !empty($last) && $last = $last->toArray();

        if ( !empty($last) && time()-$last['create_time']<3600*self::HOURS )
        {
            return out([
                'left_time'=>$goldRun['num']-$count-1
                ,'next_second'=>  3600*self::HOURS - (time()-$last['create_time'])
                ,'is_add_gold'=>0
                ,'gold'=>0
            ],'200','请稍后重试');
        }

        $func = function () use ( &$userInfo ,&$goldRun  )
        {
            UserTackRecord::createRecord($userInfo['c_user_id'],$goldRun['key_code']);
        };

        //默认数据
        $goldData = [
            'user_id'=>$userInfo['c_user_id']
            ,'gold_tribute'=>$goldRun['gold_flag']
            ,'status'=>2
            ,'type'=>$goldRun['id']
            ,'type_key'=>$goldRun['key_code']
            ,'title'=>$goldRun['title']
            ,'type_task_id'=>0
            ,'father_gold_tribute'=> 0
            ,'grandfather_gold_tribute'=>0
            ,'tribute_status'=>0
            ,'tribute_title'=>''
            ,'func'=>$func
        ];


        try
        {
            $gold = Gold::addUserGold($goldData);
        }
        catch (\Exception $e)
        {
            return out([],'10002','请重试!');
        }

        if ($gold)
        {
            return out([
                'left_time'=>$goldRun['num']-$count-1
                ,'next_second'=>3600*self::HOURS
                ,'is_add_gold'=>1
                ,'gold'=>$goldRun['gold_flag']
            ]);
        }
        else
        {
            return out([],'10002','请重试!');
        }

    }

}
