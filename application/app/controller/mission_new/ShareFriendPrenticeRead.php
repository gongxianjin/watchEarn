<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use app\app\library\UserTackRecord;
use app\model\GoldRun;
use app\model\User;
use app\model\UserTaskRecord;
use think\Cookie;
use think\Db;
use app\model\GoldProductRecord;
use time\TimeUtil;




class ShareFriendPrenticeRead  implements MissionInterface
{
    const WEEK_MAX_GOLD = 2000;
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
     * 分享朋友圈阅读获得金币
     * @return array|mixed
     * @throws
     */
    function handler()
    {
        $uid = input("uid",0);

        $userInfo = User::where(['c_user_id'=>$uid])->find();

        if(empty($userInfo)){
            return out("",10001,"error");
        }
        $goldRun = &$this->goldRun;
        if(Cookie::get('read')){
            return out("",10001,"error");
        }
        //判断一周是否回去到了 2000金币
        $sdefaultDate = date("Y-m-d");  
         //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期  
        $first=1;  
         //获取当前周的第几天 周日是 0 周一到周六是 1 - 6  
        $w=date('w',strtotime($sdefaultDate));  
         //获取本周开始日期，如果$w是0，则表示周日，减去 6 天  
        $week_start=date('Y-m-d H:i:s',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));  
         //本周结束日期  
        $week_end=strtotime("$week_start +7 days");
        $week_start =strtotime($week_start);
        $weekCount = GoldProductRecord::where(['user_id'=>$uid,"type_key"=>$goldRun['key_code']])->whereTime("create_time","w")->sum("gold_tribute");
        if($weekCount > SELF::WEEK_MAX_GOLD){
            return out("",10001,"error");
        }
        //匿名执行
        $func = function () use ( &$data ,&$GoldRun ,&$GoldRunExt )
        {
            //添加到用户任务完成记录表(带确认表信息)
            UserTackRecord::createRecord($uid,$GoldRun['key_code']);
            //$GoldRunExt->addUserGoldExtAll($data,$GoldRun);
        };
        $data = [
               "user_id"=>$uid,
               "gold_tribute"=>$goldRun['gold_flag'],
               "type"=>$goldRun['id'],
               "type_key"=>$goldRun['key_code'],
               "title"=> "好友阅读奖励",
               "status"=>1,
        ];
        $gold=Gold::addUserGold($data);
        if(!$gold){
             return out('',10001 , 'error');
        }
        Cookie::set('read','1',86400);
        return out([]);
    }

}
