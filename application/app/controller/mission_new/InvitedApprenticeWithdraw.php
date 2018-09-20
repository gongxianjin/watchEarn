<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\UserTackRecord;
use app\model\Config;
use app\model\FConfig;
use app\model\GoldProductRecord;
use app\model\GoldRun;
use app\model\InvitedApprenticeDailyRecord;
use app\model\UserApprentice;
use app\model\UserCashRecord;
use think\Db;

class InvitedApprenticeWithdraw  implements MissionInterface
{
    

    private static $GOLD_RUN_ID = 1002;
    private static $REWARD = 2;
    private static $EXCHANGE_RATE_NAME = 'exchange_rate';
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
        return out();
    }

    /**
     *
     * 当用户完成观看后
     * @return array|mixed
     * @throws
     */
    function handler()
    {

        // 模拟用户数据
        $userInfo = &$this->userInfo;

        return $this->run($userInfo);

    }

    function run(&$userInfo )
    {
        $fatherId = $userInfo['user_father_id'];
        if (empty($fatherId))
            return out([],'10002','无师傅');

        $sql = '
        SELECT
    count(1) as count
FROM
    hs_user_cash_record
where
	user_id in (select user_id from hs_user where user_father_id = ?)
limit 1;
';

        $userCount = UserCashRecord::query($sql,[$fatherId]);
        $userCount = $userCount[0]['count'];

        if ($userCount<=2)
            return out([],'10002','已发放两次奖励');

        $this->goldRun = GoldRun::find(self::$GOLD_RUN_ID);
        $func = null;
        if ($userCount==2)
        {
            //匿名执行
            $func = function () use (&$userInfo)
            {
//                UserTackRecord::createRecord($userInfo['c_user_id'],$this->goldRun['key_code']);
                //完成首邀两名徒弟
                UserTackRecord::createRecord($userInfo['c_user_id'],HiretwoStudent::GOLD_SEARCH_RUN_CODE);
            };
        }

        $exchangeRate = FConfig::where('name','eq',self::$EXCHANGE_RATE_NAME)->value('value');
        if ($exchangeRate==null)
            $exchangeRate = 1;
        $reward = self::$REWARD * 100 * $exchangeRate;

        //默认数据
        $goldData = [
            'user_id'=>$fatherId
            ,'gold_tribute'=>$reward
            ,'status'=>2
            ,'type'=>$this->goldRun['id']
            ,'type_key'=>$this->goldRun['key_code']
            ,'title'=>$this->goldRun['title']
            ,'func'=>$func
        ];
        Gold::addUserGold($goldData);

       return out(['gold'=>$reward]);
    }

}
