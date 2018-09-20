<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\UserTackRecord;
use app\model\Config;
use app\model\EffectiveApprenticeRecord;
use app\model\FConfig;
use app\model\GoldProductRecord;
use app\model\GoldRun;
use app\model\User;

class GetEffectiveApprentice extends BaseController implements MissionInterface
{

    private static $SEARCH_KEY_CODE = [
        'usual_news_read'
        ,'read_push_messages'
        ,'usual_read'
        ,'red_read'
        ,'high_quality_review_awards'
        ,'hot_search_reward'
    ];

    private static $GOLD_RUN_ID = 10001;
    private static $GOLD_RUN_CODE = 'effective_apprentice';

    private static $EXCHANGE_RATE_NAME = 'exchange_rate';

    private static $config =[
        1=>['reward'=>4,'complete'=>false,'income'=>4]
        ,2=>['reward'=>4,'complete'=>false,'income'=>8]
        ,4=>['reward'=>10,'complete'=>false,'income'=>18]
        ,6=>['reward'=>14,'complete'=>false,'income'=>32]
        ,8=>['reward'=>16,'complete'=>false,'income'=>48]
        ,15=>['reward'=>50,'complete'=>false,'income'=>98]
        ,30=>['reward'=>118,'complete'=>false,'income'=>216]
        ,60=>['reward'=>252,'complete'=>false,'income'=>468]
        ,120=>['reward'=>500,'complete'=>false,'income'=>968]
        ,320=>['reward'=>1920,'complete'=>false,'income'=>2888]
    ];

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
        $userInfo = &$this->userInfo;
        $config = self::$config;

        //有效徒弟数量
        $effectiveApprenticeCount = EffectiveApprenticeRecord::where(['user_father_id'=>$userInfo['c_user_id']])->count();

        $reward = $config[array_keys($config)[count($config)-1]];
        $income = 0;
        $stage = 1;
        foreach ($config as $k=>&$v)
        {

            if ($effectiveApprenticeCount >= $k)
            {
                $stage++;
                $reward = $v['reward'];
                $income = $v['income'];
                $v['complete'] = true;
            }
            else
            {
                $reward = $v;
                break;
            }

        }

        $apprenticeCount  = User
            ::where('user_father_id','eq',$userInfo['c_user_id'])
            ->count();

        $data = [
            'reward'=>$reward//本阶段奖励
            ,'effective_apprentice'=>$effectiveApprenticeCount//有效徒弟个数
            ,'income'=>$income//收入
            ,'apprentice_count'=>$apprenticeCount//徒弟数量
            ,'data'=>$config
            ,'stage'=>$stage
        ];

        return out($data);
    }

    /**
     *
     * 收取徒弟
     * @return array|mixed
     * @throws
     */
    function handler()
    {

        $goldRun = &$this->goldRun;

        $userInfo = &$this->userInfo;

        return $this->run($userInfo,$goldRun);
    }

    function run(&$userInfo)
    {

        if ($userInfo['user_father_id']==0)
            return out([],'10002','未招收徒弟');

        //搜索每日金币
        $startTime = mktime(0,0,0);
        $endTime = mktime(24,0,0);

        if (EffectiveApprenticeRecord::find([
            'user_id'=>$userInfo['c_user_id']
            ,'user_father_id'=>$userInfo['user_father_id']
        ]))
        {
            return out([],'10002','已成为有效徒弟');
        }

        $dailyData = (new GoldProductRecord())->getDailyUsage(
            $userInfo['c_user_id']
            ,self::$SEARCH_KEY_CODE
            ,$startTime
            ,$endTime
        );

        if (empty($dailyData) || $dailyData[0]['gold']<100)
        {
            return out([],'10002','请继续阅读');
        }

        $config = self::$config;
        $configKeys = array_keys($config);

        $count = EffectiveApprenticeRecord
            ::where([
                'user_father_id'=>$userInfo['user_father_id']
            ])
            ->count();

        $count++;

        $rewardMoney = in_array($count,$configKeys) ?$config[$count]['reward']:0;

        if ($rewardMoney===0)
        {
            //成为有效徒弟
            (new EffectiveApprenticeRecord)->save([
                'user_id'=>$userInfo['c_user_id']
                ,'user_father_id'=>$userInfo['user_father_id']
            ]);
            return out([],'10002','快去收取更多徒弟，获得下一阶段的奖励');
        }


        $exchangeRate = FConfig::where('name','eq',self::$EXCHANGE_RATE_NAME)->value('value');
        if ($exchangeRate==null)
            $exchangeRate = 1;

        $rewardGold = $rewardMoney*100*$exchangeRate;

        $func = function () use
        (
            &$userInfo
        )
        {
            UserTackRecord::createRecord($userInfo['c_user_id'],self::$GOLD_RUN_CODE);

            //成为有效徒弟
            (new EffectiveApprenticeRecord)->save([
                'user_id'=>$userInfo['c_user_id']
                ,'user_father_id'=>$userInfo['user_father_id']
            ]);

        };

        //默认数据
        $goldData = [
            'user_id'=>$userInfo['user_father_id']
            ,'gold_tribute'=>$rewardGold
            ,'status'=>2
            ,'type'=>self::$GOLD_RUN_ID
            ,'type_key'=>self::$GOLD_RUN_CODE
            ,'title'=>'完成收徒大吉 -'.$count.'人'
            ,'func'=>$func
        ];

        $result = Gold::addUserGold($goldData);

        if ($result)
            return out([],200,'收徒成功');
        else
            return out([],'10002','请稍后重试');
    }

}
