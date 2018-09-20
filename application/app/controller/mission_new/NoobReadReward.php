<?php
namespace app\app\controller\mission_new;


use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use app\app\library\UserTackRecord;
use app\model\GoldProductRecord;
use app\model\GoldRun;
use app\model\TempUser;
use app\model\User;
use app\model\UserTaskRecord;
use think\Request;

class NoobReadReward extends BaseController implements MissionInterface
{
    const GOLD_SEARCH_RUN_CODE = 'usual_read';
    const DAYS = 30;
    const DAYS_SECOND = 2592000;

    private static $GOLD_RUN_ID = 25;
    private static $GOLD_RUN_KEY_CODE = 'noob_read_reward';
    private static $GOLD_RUN_SYCLE = 10;

    /**
     * @var GoldRun
     */
    protected $goldRun = null;

    function _initGoldRun(&$goldRun)
    {
        $this->goldRun = $goldRun;
    }

    /**
     * @return mixed|\think\response\Json
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function info()
    {
        $goldRun = &$this->goldRun;

        $userInfo = &$this->userInfo;

        //时间计算
        $registerDateTime = new \DateTime();
        $registerDateTime->setTimestamp($userInfo['create_time']);
        $registerDateTime->setTime(0,0,0);
        $endTime = new \DateTime();
        $endTime->setTimestamp($registerDateTime->getTimestamp()+self::DAYS_SECOND);

        $dailyData = (new GoldProductRecord())->getDailyUsage(
            $userInfo['c_user_id']
            ,[self::$GOLD_RUN_KEY_CODE]
            ,$registerDateTime->getTimestamp()
            ,$endTime->getTimestamp()
        );

        $nowDate = new \DateTime();
        $nowDate->setTime(0,0,0);
        $days = $nowDate->diff($registerDateTime)->days;

        $data = [
            'days_finish'=>[]
        ];

        $isComplete = null;
        $tempDate = null;
        $finishGold = 0;
        for ($i=0;$i<self::DAYS;$i++)
        {
            $isComplete = false;
            $tempDate = clone $registerDateTime;
            $incInterval = new \DateInterval(sprintf("P{$i}D"));
            $tempDateString = $tempDate->add($incInterval)->format('Ymd');

            //当前天数小于最大日期后 均为null
            if ($i>$days)
            {
                $data['days_finish'][$tempDate->format('M j')]=null;
                continue;
            }

            if ($dailyData!=null )
            {

                foreach ($dailyData as $index=>&$daily)
                {
                    $daily instanceof GoldProductRecord && $daily = $daily->toArray();

                    if (
                        $daily['date'] === $tempDateString &&
                        $daily['gold'] >= 50
                    )
                    {
                        $isComplete = true;
                        $data['days_finish'][$tempDate->format('M j')]=$daily['gold'];
                        $finishGold += $daily['gold'];
                        array_splice($dailyData,$index,1);  //剔除已检查
                    }
                }
                if ($isComplete===true) continue;
            }

            $data['days_finish'][$tempDate->format('M j')]=false;
        }

        //修复数据
        if ( isset($data['days_finish'][$nowDate->format('M j')])
            && $data['days_finish'][$nowDate->format('M j')]===false )
            $data['days_finish'][$nowDate->format('M j')] = null;

        $data['finish_gold'] = $finishGold;

        /* 计算剩余 */
        $registerGold = [ 0=>100,1=>200,2=>400 ];
        $gold = [4=>400,8=>600,12=>800,16=>1000,20=>1000,24=>1000,28=>1000];
        $daysFinishKey = array_keys($data['days_finish']);
        $index = 0;
        $j=0;
        $potentialGold = 0;
        for ($i=0;$i<self::DAYS;$i++)
        {
            //日期
            $tempDate = clone $registerDateTime;
            $tempDate->add(new \DateInterval(sprintf("P{$i}D")));

            if ($i<3)
            {
                if($data['days_finish'][$daysFinishKey[$i]] === null)
                {
                    $data['days_finish'][$tempDate->format('M j')]=$registerGold[$i]*-1;
                    $potentialGold += $registerGold[$i]*-1;
                }
            }

            if ( $j>0  && $data['days_finish'][$daysFinishKey[$i]] ===false) //如果 中途有未完成则重现计算
            {
                $j=0;
                $index=0;
                continue;
            }else if ($data['days_finish'][$daysFinishKey[$i]]===false)//未完成直接跳过
                continue;

            $j++;
            if ($j===29) break;//循环到29天时直接完成整个循环


            if ($j<array_keys($gold)[$index])//开始计算
                continue;
            else if ($j==array_keys($gold)[$index])//满足下一奖励的条件
            {

                if ($data['days_finish'][$tempDate->format('M j')]===null)
                {
                    $data['days_finish'][$tempDate->format('M j')]=$gold[array_keys($gold)[$index]]*-1;
                    $potentialGold += $gold[array_keys($gold)[$index]]*-1;
                }

                $index++;
            }

        }
        $data['potential_gold'] = $potentialGold*-1;

        return out($data);
    }

    /**
     * 基础信息
     * @return \think\response\Json
     */
    function reward()
    {
        return out([
            ['content'=>'注册第一天获得50以上阅读金币','reward'=>'100']
            ,['content'=>'注册第二天获得50以上阅读金币','reward'=>'200']
            ,['content'=>'注册第三天获得50以上阅读金币','reward'=>'400']
            ,['content'=>'连续4天，每天获得50以上阅读金币','reward'=>'400']
            ,['content'=>'连续8天，每天获得50以上阅读金币','reward'=>'600']
            ,['content'=>'连续12天，每天获得50以上阅读金币','reward'=>'800']
            ,['content'=>'连续16天，每天获得50以上阅读金币','reward'=>'1000']
            ,['content'=>'连续20天，每天获得50以上阅读金币','reward'=>'1000']
            ,['content'=>'连续24天，每天获得50以上阅读金币','reward'=>'1000']
            ,['content'=>'连续28天，每天获得50以上阅读金币','reward'=>'1000']
        ]);
    }

    /**
     * @return mixed|\think\response\Json
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function handler()
    {
        // 模拟用户数据
        $userInfo = &$this->userInfo;

        $goldRun = &$this->goldRun;

        return $this->run($userInfo,$goldRun);
    }

    /**
     * @param $userInfo
     * @param $goldRun
     * @return \think\response\Json
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function run(&$userInfo )
    {
        $goldProductRecord = new GoldProductRecord();

        $todayOnce = $goldProductRecord
            ->where('create_time','between',[mktime(0,0,0),mktime(24,0,0)])
            ->where('user_id','eq',$userInfo['c_user_id'])
            ->where('type_key',self::$GOLD_RUN_KEY_CODE)
            ->count();

        if ($todayOnce!=0)
            return out([], "10002", "请明天重试。");

        $registerDateTime = new \DateTime();
        $registerDateTime->setTimestamp($userInfo['create_time']);
        $registerDateTime->setTime(0,0,0);
        $endTime = new \DateTime();
        $endTime->setTimestamp($registerDateTime->getTimestamp()+self::DAYS_SECOND);

        //搜索每日金币
        $dailyData = $goldProductRecord->getDailyUsage(
            $userInfo['c_user_id']
            ,[self::GOLD_SEARCH_RUN_CODE]
            ,$registerDateTime->getTimestamp()
            ,$endTime->getTimestamp()
        );

        $nowDate = new \DateTime();
        $nowDate->setTime(0,0,0);
        $days = $nowDate->diff($registerDateTime)->d+1;
        $func = null;
        $goldTribute = null;
        $title = null;
        $titleFunc = function ($days){
            $dayIndex = 0;

            switch ($days%10)
            {
                case 1:
                    $dayIndex = $days.'st';
                    break;
                case 2:
                    $dayIndex = $days.'nd';
                    break;
                case 3:
                    $dayIndex = $days.'rd';
                    break;
            }

            return "Reward for novice $dayIndex day watch : Over 50 coins";
        }; ; //todo 翻译
        $tempDate = null;
        $isReward = false;
        $totalGold = 0;

        if ($days<4)//前三天
        {

            $tempDate = $registerDateTime;
            $dayIndex = $days-1;
            $dateString = $tempDate->add((new \DateInterval("P{$dayIndex}D")))->format('Ymd');
            //是否能够领取奖励
            foreach ($dailyData as $index=>&$daily)
            {
                $daily instanceof GoldProductRecord && $daily = $daily->toArray();

                if ($daily['date']===$dateString)
                {
                    $totalGold += $daily['gold'];

                    if ($totalGold>=50)
                    {
                        $isReward = true;
                        break;
                    }
                }
            }

            if ($isReward===true)
            {
                $goldTributes = function ($date)
                {
                    $gold = [
                        1=>100
                        ,2=>200
                        ,3=>400
                    ];
                    return isset($gold[$date])?$gold[$date]:false;
                };
                $goldTribute = $goldTributes($days);

                $title = $titleFunc($days); //todo 翻译
            }
            else
                return out([],'10002','未获得50阅读金币');

        }
        else
        {
            $gold = [
                4=>400
                ,8=>600
                ,12=>800
                ,16=>1000
                ,20=>1000
                ,24=>1000
                ,28=>1000
            ];

            $dailyData = array_reverse($dailyData);

            $count = 0;
            $nowDateTimpstamp = $nowDate->getTimestamp();

            foreach ($dailyData as $index=>&$daily)
            {
                $daily instanceof GoldProductRecord && $daily = $daily->toArray();

                if (strtotime($daily['date'])=== $nowDateTimpstamp-($index*3600*24))
                    $count++;
                else
                    break;

            }

            if (in_array($count,array_keys($gold)))
                $isReward = true;
            else
                return out([],'10002','没有到达目标点，请继续坚持');

            if ($isReward===true)
            {
                $goldTribute = $gold[$count];
                $title = $titleFunc($days);
            }
            else
                return out([],'10002','未连续获得50阅读金币');//todo 翻译

        }

        //匿名执行
        $func = function () use
        (
            &$userInfo
            ,&$goldTribute
            ,&$title
        )
        {
            //UserTackRecord::createRecord($userInfo['c_user_id'],self::$GOLD_RUN_KEY_CODE);
            (new GoldRunExt())->addTaskLogInvoice([
                'user_id'=>$userInfo['c_user_id']
                ,'task_id'=>self::$GOLD_RUN_ID
                ,'hours_at'=>0
                ,'key_code'=>self::$GOLD_RUN_KEY_CODE
                ,'type'=>1
                ,'status'=>2
                ,'gold_tribute'=>$goldTribute
                ,'father_gold_tribute'=>0
                ,'grandfather_gold_tribute'=>0
                ,'is_del'=>1
                ,'description'=>$title
            ],[
                'sycle'=>self::$GOLD_RUN_SYCLE
            ]);
        };

        //入库
        $gold = new Gold();

        $status = 1;
        $type = self::$GOLD_RUN_ID;
        $typeKey = self::$GOLD_RUN_KEY_CODE;
        //默认数据
        $goldData = [
            'user_id'=>$userInfo['c_user_id']
            ,'gold_tribute'=>$goldTribute
            ,'status'=>$status
            ,'type'=>$type
            ,'type_key'=>$typeKey
            ,'title'=>$title
            ,'func'=>$func
        ];
        try
        {

            $gold->addUserGold($goldData);
        }
        catch (\Exception $e)
        {
            return out([], "10002", $e->getMessage());
        }

        return out([],'200','验证完成，快去开红包吧');
    }


}
