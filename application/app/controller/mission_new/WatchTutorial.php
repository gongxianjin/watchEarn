<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use app\app\library\UserTackRecord;
use app\model\GoldRun;
use app\model\UserTaskRecord;

class WatchTutorial extends BaseController implements MissionInterface
{

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
        $goldRun = &$this->goldRun;

        // 模拟用户数据
        $userInfo = &$this->userInfo;

        //是否观看过教程
        if ( $goldRun['is_activation'] == 0 )
            return out("", "10002", "task closed");

        $isWatchTutorial = (new UserTaskRecord)->once($userInfo['c_user_id'],$goldRun['key_code']);

        if ( $isWatchTutorial!==null )
            return out("","10002","You've watched tutorial video");

        //匿名执行
        $func = function () use
        (
            &$userInfo
            ,&$goldRun
        )
        {
            UserTackRecord::createRecord($userInfo['c_user_id'],$goldRun['key_code']);
            (new GoldRunExt())->addUserGoldExtAll([
                'user_id'=>$userInfo['c_user_id']
                ,'task_id'=>$goldRun['id']
                ,'hours_at'=>0
                ,'key_code'=>$goldRun['key_code']
                ,'type'=>$goldRun['type']
                ,'status'=>1
                ,'num'=>1
                ,'sycle'=>0
                ,'gold_tribute'=>$goldRun['gold_flag']
                ,'father_gold_tribute'=>0
                ,'grandfather_gold_tribute'=>0
                ,'is_del'=>1
                ,'description'=>'完成新人问答任务'
            ],$goldRun);
        };

        //入库
        $gold = new Gold();
        $goldTribute = $goldRun['gold_flag'];
        $status = 2;
        $type = $goldRun['id'];
        $typeKey = $goldRun['key_code'];
        $title = 'Watch Tutorial Video';

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
            return out("", "10002", $e->getMessage());
        }

        return out(null,'200','success! + 100 Coins');
    }

}
