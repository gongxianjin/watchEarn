<?php
namespace app\app\controller\mission_new;

use app\app\library\Gold;
use app\app\library\UserTackRecord;
use app\model\Config;
use app\model\FConfig;
use app\model\GoldRun;
use app\model\User;
use think\Request;
use app\model\UserApprentice;

class HiretwoStudent implements MissionInterface
{
    const GOLD_SEARCH_RUN_CODE = 'hire_two_student';
    protected $goldRun = null;

    private static $EXCHANGE_RATE_NAME = 'exchange_rate';
    private static $REWARD_MONEY = 2;

    private static $GOLD_RUN_ID = 17;
    private static $GOLD_RUN_KEY_CODE = 'hire_two_student';

    /**
     * @var GoldRun
     */
    function _initGoldRun(&$goldRun)
    {
        $this->goldRun = $goldRun;
    }


    /**
     * 信息
     * @return mixed
     */
    function info()
    {
        // TODO: Implement info() method.
    }

    /**
     *
     */
    function handler()
    {


    }

    /**
     * 首次邀请2名徒弟新手任务
     *
     * @param $userInfo
     * @return \think\response\Json
     * @throws \Exception
     */
    function run($userInfo)
    {

        $fatherId  = User::where('c_user_id','eq',$userInfo['c_user_id'])->value('user_father_id');

        if (empty($fatherId))
            return out([],'10002','没有师傅');

        $count = User::where('user_father_id','eq',$fatherId)->where('user_father_id','neq',0)->count();

        if ($count!=2)
            return out([],'10002','未满足条件');

        $exchangeRate = FConfig::where('name','eq',self::$EXCHANGE_RATE_NAME)->value('value');
        if ($exchangeRate==null)
            $exchangeRate = 1;

        $rewardMoney = self::$REWARD_MONEY;
        //修改为 首邀两名徒弟  给0.4元，剩余0.6 元在徒弟连续阅读7天（邀请收徒任务）结算
        $rewardGold = $rewardMoney*10*$exchangeRate*2;

        //匿名执行
        $func = function () use ( &$fatherId  )
        {
            UserTackRecord::createRecord($fatherId,self::GOLD_SEARCH_RUN_CODE);
        };

        //默认数据
        $goldData = [
            'user_id'=>$fatherId
            ,'gold_tribute'=>$rewardGold
            ,'status'=>2
            ,'type'=>self::$GOLD_RUN_ID
            ,'type_key'=>self::$GOLD_RUN_KEY_CODE
            ,'title'=>'首次邀请两名徒弟完成'
            ,'func'=>$func
        ];

        $result = Gold::addUserGold($goldData);

        if ($result)
            return out([],'200','任务完成');
        else
            return out([],'10002','出现错误');
    }


}
