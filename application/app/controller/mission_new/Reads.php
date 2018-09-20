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

class Reads extends BaseController implements MissionInterface
{

    private static $SEARCH_KEY_CODE = [
        //'view_ad'
        'usual_news_read'
        ,'son_usual_news_read'
        ,'grandson_usual_news_read'
        ,'read_push_messages'
        ,'usual_read'
        ,'son_usual_read'
        ,'grandson__usual_read'
        ,'red_read'
        ,'son_red_read'
        ,'grandson__red_read'
        ,'hot_search_reward'
    ];

    const READ_VALVE_KEY = 'read_valve';
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
            return out(['gold_flag'=>0,'count'=>0],'10002','Suspicious activiity detected, your account has been blocked!');

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
                ->where('title','in',['Videos Coins','News Coins'])
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

        $dayReadGetCount = FConfig::where('name','eq','day_read_get_count')->value('value');

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
            return out(array('gold_flag'=>0,'count'=>$count),'10002','You\'ve viewed all dollar-marked videos for you today ');

        //特权师傅4~7倍奖励
        $data['father_gold_tribute']=$data['gold_tribute']*$this->fatherMultiple;
        $father_title=getArrVal($this->userInfo,'nickname') . $goldRun['title'];
        //匿名执行
        $func = function () use ( &$data ,&$goldRun ,&$goldRunExt )
        {
            $goldRunExt->addUserGoldExtAll($data,$goldRun);
        };

        //$user_id, $gold_tribute, $status, $type, $type_key, $title, $type_task_id = 0, $father_gold_tribute = 0, $grandfather_gold_tribute = 0, $tribute_status = 0, $tribute_title = '', $func = null


        //师傅进贡
        $isTribute = true;
        if ( in_array($goldRun['key_code'],[ 'view_ad' ]) )
            $isTribute = false;
        else if ( $userInfo['user_father_id'] != 0 )
        {

            $dailyMaxReadFen = Grade::join('hs_user','hs_user.grade_id = hs_grade.id','left')
                ->where('c_user_id','eq',$userInfo['user_father_id'])
                ->value('daily_max_read_contribute_fen');

            $exchangeRate = FConfig::where('name','eq',self::EXCHANGE_RATE_VALVE_KEY)->value('value');

            $gold = GoldProductRecord
                ::where('type_key','in',self::$SEARCH_KEY_CODE)
                ->where('create_type','eq',2)
                ->where('user_id','eq',$userInfo['user_father_id'])
                ->where('create_time','between',[mktime(0,0,0),mktime(23,59,59)])
                ->value(' sum(gold_tribute) as gold');

            if ($gold==null)
                $gold = 0;

            if ($gold >= $dailyMaxReadFen * $exchangeRate)
            {
                $isTribute = false;
            }

        }

        //默认数据
        $goldData = [
            'user_id'=>$data['user_id']
            ,'gold_tribute'=>$data['gold_tribute']
            ,'status'=>2
            ,'type'=>$data['task_id']
            ,'type_key'=>$data['key_code']
            ,'title'=>$goldRun['title']
            ,'type_task_id'=>0
            ,'father_gold_tribute'=> $isTribute ? $data['father_gold_tribute'] : 0
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
