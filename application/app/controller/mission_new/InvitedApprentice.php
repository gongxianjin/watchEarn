<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;
use app\app\library\Gold;
use app\model\GoldProductRecord;
use app\model\GoldRun;
use app\model\InvitedApprenticeDailyRecord;
use app\model\UserApprentice;
use think\Db;

class InvitedApprentice extends BaseController implements MissionInterface
{

    private static $SEARCH_KEY_CODE = [
        'usual_news_read'
        ,'read_push_messages'
        ,'usual_read'
        ,'red_read'
//        ,'high_quality_review_awards'
        ,'hot_search_reward'
    ];

    const APPRENTICE_TITLE = 'Friends Coins';
    const SUB_APPRENTICE_TITLE = '2nd-class friends Coins';

    const GOLD_RUN_TYPE = 9;
    const GOLD_RUN_TYPE_CODE = 'invite_an_apprentice';

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

        $fatherReward = Father::getInvitedApprenticeReward($userInfo['create_time']);
        $grandfatherReward = Father::getInvitedSubApprenticeReward($userInfo['create_time']);

        if ($fatherReward==0 && $grandfatherReward==0)
            return out([],'10002','无法满足邀请收徒条件');

        $invitedApprenticeDailyRecord = new InvitedApprenticeDailyRecord();

        if ($invitedApprenticeDailyRecord->getDailyUsage($userInfo['c_user_id'],time())!=false)
        {
            return out([],'10002','今日已完成');
        }

        //每日阅读获得的金币量
        $startTime = mktime(0,0,0);
        $endTime = mktime(24,0,0);
        $result = (new GoldProductRecord())->getDailyUsage($userInfo['c_user_id'],self::$SEARCH_KEY_CODE,$startTime,$endTime);

        if (empty($result))
            return out([],'10002','未满足50金币，请继续阅读');

        $gold = $result[0]->toArray()['gold'];

        if ($gold<50)
            return out([],'10002','未满足50金币，请继续阅读');

        Db::startTrans();
        try
        {
            //向师傅进贡
            if ($userInfo['user_father_id']!= 0 && $fatherReward>0)
            {
                $this->apprenticeReward($userInfo ,$fatherReward);
            }

            // 向师祖进贡会出现的问题
            // 是为前两名徒弟

            $ids = UserApprentice
                ::where('master_user_id','eq',$userInfo['user_father_id'])
                ->field('apprentice_user_id')
                ->order('id')
                ->limit(2)
                ->select();

            foreach ($ids as &$item)
                $item = $item->toArray();
            $ids = array_column($ids,'apprentice_user_id');

            // 当 徒弟绑定师傅 会出现一下的异常情况
            // 场景（以徒弟为视角）
            // 当 用户B做为师傅，用户C、用户D输入B的邀请码成为徒弟后
            // 用户B在输入用户A的邀请码，用户A则为用户B的师傅，但不是用户C、D的师祖(收徒逻辑)
            // 但是以后用户E、F、G输入师傅B的邀请码后，能正常成为A的徒孙
            // 由于前两名为数据库中没有师祖ID值为空 所以不会添加金币

            if ($userInfo['user_grandfather_id']!= 0 && $grandfatherReward>0 && in_array($userInfo['c_user_id'],$ids))
            {
                $this->subApprenticeReward($userInfo ,$grandfatherReward);
            }

            if (
                ($userInfo['user_grandfather_id']!= 0 && $grandfatherReward>0)
                ||
                ($userInfo['user_father_id']!= 0 && $fatherReward>0)
            )
            {
                $exists = $invitedApprenticeDailyRecord
                    ->where('user_id','eq',$userInfo['c_user_id'])
                    ->where('date','eq',date('Ymd'))
                    ->count();

                if (empty($exists))
                    $invitedApprenticeDailyRecord->insert([
                        'user_id'=>$userInfo['c_user_id']
                        ,'date'=>date('Ymd')
                    ]);

            }

            Db::commit();
        }
        catch (\Exception $e)
        {
            Db::rollback();
            return out([],'10002','发生错误，请稍后再试');
        }

        return out([],'200','任务完成');
    }

    /**
     * 师傅进贡
     *
     * @param $userInfo
     * @param $goldTribute
     * @throws \Exception
     */
    private function apprenticeReward(&$userInfo ,$goldTribute)
    {
        echo "向师傅贡献{$userInfo['nickname']}\n";
        $func = function () use
        (
            &$userInfo
            ,&$goldTribute
        )
        {
            //增加师傅于徒弟的关系金币
            UserApprentice::update([
                'gold_tribute_total'=>['exp','gold_tribute_total+' . $goldTribute]
            ],[
                'master_user_id'=>$userInfo['user_father_id']
                ,'apprentice_user_id'=>$userInfo['c_user_id']
                ,'type'=>1
            ]);
        };
        //todo 添加收徒弟数量
        $goldGold = [
            'user_id'=>$userInfo['user_father_id']
            ,'gold_tribute'=>$goldTribute
            ,'status'=>2
            ,'type'=>self::GOLD_RUN_TYPE
            ,'type_key'=>self::GOLD_RUN_TYPE_CODE
            ,'title'=>self::APPRENTICE_TITLE . " “{$userInfo['nickname']}”"
            ,'type_task_id'=>0
            ,'father_gold_tribute'=>0
            ,'grandfather_gold_tribute'=>0
            ,'tribute_status'=>0
            ,'tribute_title'=>0
            ,'func'=>$func
        ];

        Gold::addUserGold($goldGold);
    }

    /**
     * 师祖进贡
     *
     * @param $userInfo
     * @param $goldTribute
     * @throws \Exception
     */
    private function subApprenticeReward(&$userInfo ,$goldTribute)
    {
        $func = function ()use
        (
            &$userInfo
            ,&$goldTribute
        )
        {
            //增加师祖于徒孙的关系金币
            UserApprentice::update([
                'gold_tribute_total'=>['exp','gold_tribute_total+' . $goldTribute]
            ],[
                'master_user_id'=>$userInfo['user_grandfather_id']
                ,'apprentice_user_id'=>$userInfo['c_user_id']
                ,'type'=>2
            ]);
        };
        //todo 添加收徒弟数量
        $goldGold = [
            'user_id'=>$userInfo['user_grandfather_id']
            ,'gold_tribute'=>$goldTribute
            ,'status'=>2
            ,'type'=>self::GOLD_RUN_TYPE
            ,'type_key'=>self::GOLD_RUN_TYPE_CODE
            ,'title'=>self::SUB_APPRENTICE_TITLE . " “{$userInfo['nickname']}”"
            ,'type_task_id'=>0
            ,'father_gold_tribute'=>0
            ,'grandfather_gold_tribute'=>0
            ,'tribute_status'=>0
            ,'tribute_title'=>0
            ,'func'=>$func
        ];

        Gold::addUserGold($goldGold);
    }


}
