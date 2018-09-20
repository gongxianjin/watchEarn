<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use app\app\library\UserTackRecord;
use app\model\GoldRun;
use app\model\UserTaskRecord;
use think\Db;


class Treasure extends BaseController implements MissionInterface
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
     * 开宝箱
     * @return array|mixed
     * @throws
     */
    function handler()
    {
        $goldRun = &$this->goldRun;

        // 模拟用户数据
        $userInfo = &$this->userInfo;

        $goldRunExt = new GoldRunExt();

        $data=array();
        $data['user_id']=$userInfo['c_user_id'];
        $data['task_id']=$goldRun['id'];
        $data['key_code']=$goldRun['key_code'];
        $data['type']=$goldRun['type'];
        $data['gold_tribute']=$goldRun['gold_flag_max']>$goldRun['gold_flag']?mt_rand($goldRun['gold_flag'],$goldRun['gold_flag_max']):$goldRun['gold_flag'];
        $father_gold_tribute=$goldRun['gold_tribute_max']>$goldRun['gold_tribute']?mt_rand($goldRun['gold_tribute'],$goldRun['gold_tribute_max']):$goldRun['gold_tribute'];
        $data['father_gold_tribute']=$father_gold_tribute*$this->fatherMultiple;
        $data['grandfather_gold_tribute']=$goldRun['gold_grandfather_max']>$goldRun['gold_grandfather']?mt_rand($goldRun['gold_grandfather'],$goldRun['gold_grandfather_max']):$goldRun['gold_grandfather'];
        $data['status']=1;
        $data['is_del']=1;

        $data['description']=$goldRun['title'];
        $data['min_time']=strtotime("-".($goldRun['sycle'])." hours");
        $sql="SELECT hours_at,gold_tribute,update_time FROM hs_task_invoice where user_id='".$data['user_id']."' and task_id='".$data['task_id']."' and update_time>='".$data['min_time']."' and status=1  order by hours_at desc limit 1;";
        $row=Db::query($sql);

        $outArr=array();
        $outArr['is']=$outArr['update_time']=$outArr['gold_tribute']=0;
        if($row){
            $outArr['update_time']=date("Y-m-d H:i:s",($row[0]['update_time']+4*60*60));
            //$outArr['time_difference']=14400-(time()-$row[0]['update_time']);
            $outArr['time_difference']=14400+$row[0]['update_time']-time();
            $outArr['is']=1;
        }else{
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
                ,'father_gold_tribute'=>$data['father_gold_tribute']
                ,'grandfather_gold_tribute'=>$data['grandfather_gold_tribute']
                ,'tribute_status'=>2
                ,'tribute_title'=>$goldRun['title']
                ,'func'=>$func
            ];

            Gold::addUserGold($goldData);
            $outArr['gold_tribute']=$data['gold_tribute'];
            $outArr['is'] = 1;
            $outArr['update_time'] = date("Y-m-d H:i:s",time()+4*60*60);
            $outArr['time_difference']=14400;
        }
        return out($outArr, 200, 'successed');
    }

}
