<?php
namespace app\app\controller\mission_new;


use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use app\model\FConfig;
use app\model\GoldProductRecord;
use app\model\GoldRun;
use app\model\Grade;
use app\model\User;

class ReadsAd extends BaseController implements MissionInterface
{

    private static $SEARCH_KEY_CODE = [
        'view_ad',
        'son_view_ad',
        'grandson_view_ad'
//        ,'usual_news_read'
//        ,'read_push_messages'
//        ,'usual_read'
//        ,'red_read'
//        ,'hot_search_reward'
    ];

    const READ_VALVE_KEY = 'ad_read_valve';
    const EXCHANGE_RATE_VALVE_KEY = 'exchange_rate';

    /**
     * @var GoldRun
     */
    protected $goldRun = null;

    function _initGoldRun(&$goldRun)
    {
        $this->goldRun = $goldRun;
    }

    /**
     * 基础信息
     * @return \think\response\Json
     */
    function info()
    {
        return out();
    }

    /**
     * 用户回答问题完成后
     *
     * @return array|mixed
     *
     * @throws
     */
    function handler()
    {
        //默认值
        $userInfo = &$this->userInfo;
        $goldRun = &$this->goldRun;

        //用户已出发过阀值
        if ($userInfo['is_cross_read_level']==1)
            return out(['gold_flag'=>0,'count'=>0],'10002','');

        //用户触发阅读阀值
        $readValve = FConfig::where('name','eq',self::READ_VALVE_KEY)->value('value');

        if (!empty($readValve) && false)
        {
            $readValve = json_decode($readValve,true);

            $readValveK = array_keys($readValve);
            $readValve = $readValve[$readValveK[0]];

            $sum = GoldProductRecord
                ::where('user_id','eq',$userInfo['c_user_id'])
                ->where('create_type','eq',1)
                ->where('create_time','gt',time()-$readValveK[0])
                ->where('type_key','in',self::$SEARCH_KEY_CODE)
                ->field('sum(gold_tribute) as total')
                ->find();

            $total = $sum->toArray()['total'];

            if ($total!= null && $total > $readValve)
            {
                User::update([
                    'status'=>2
                    ,'is_cross_read_level'=>1
                ],[
                    'c_user_id'=>$userInfo['c_user_id']
                ]);

                return out(array('gold_flag'=>0,'count'=>0),'10002','');
            }

        }

        $goldRunExt = new GoldRunExt();
        $data=$goldRunExt->newMissionValidateBaseAll($userInfo['c_user_id'],$goldRun);//验证

        //广告暂定50个
    //    $dayReadGetCount = FConfig::where('name','eq','day_read_get_count')->value('value');
        $dayReadGetCount = 10;

        $count = 0;
        $result = (new GoldProductRecord())->getDailyUsage(
            $userInfo['c_user_id']
            ,self::$SEARCH_KEY_CODE
            ,mktime(0,0,0)
            ,mktime(24,0,0)
        );

        if (!empty($result))
            $count = $result[0]->toArray()['count'];

        if ($count>=$dayReadGetCount)
            return out(array('gold_flag'=>0,'count'=>$count),'10002','can\'t get more coin');

        //特权师傅4~7倍奖励
        $father_title=getArrVal($this->userInfo,'nickname') . $goldRun['title'];
        //匿名执行
        $func = function () use ( &$data ,&$goldRun ,&$goldRunExt )
        {
            $goldRunExt->addUserGoldExtAll($data,$goldRun);
        };

        //默认数据
        $goldData = [
            'user_id'=>$data['user_id']
            ,'gold_tribute'=>$data['gold_tribute']
            ,'status'=>2
            ,'type'=>$data['task_id']
            ,'type_key'=>$data['key_code']
            ,'title'=>$goldRun['title']
            ,'type_task_id'=>0
            ,'father_gold_tribute'=> 0
            ,'grandfather_gold_tribute'=>0
            ,'tribute_status'=>2
            ,'tribute_title'=>$father_title
            ,'func'=>$func
        ];

        $gold=Gold::addUserGold($goldData);

        if ($this->login_flag==true)
        {
            //阅读奖励
            (new NoobReadReward())->run($userInfo);
            //邀请徒弟奖励
            (new InvitedApprentice())->run($userInfo);
            //收徒大吉
            //(new GetEffectiveApprentice())->run($userInfo);
        }


        if($gold){
            return out(array('gold_flag'=>$data['gold_tribute'],'count'=>$dayReadGetCount - $count - 1));
        }else{
            return out('', 502, '请稍后重试');
        }
    }

}
