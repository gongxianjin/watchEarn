<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;

use app\app\library\Gold;

use app\app\library\GoldRunExt;

use app\app\library\UserTackRecord;

use app\model\GoldRun;

use app\model\UserTaskRecord;

class TaskLogin extends BaseController implements MissionInterface
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
        $goldRun = &$this->goldRun;
        $userInfo = &$this->userInfo;

        $goldRunExt = new GoldRunExt();

        $multiple=1;

        $data=array();
        $data['user_id']=$userInfo['c_user_id'];
        $data['task_id']=$goldRun['id'];
        $data['key_code']=$goldRun['key_code'];
        $data['type']=$goldRun['type'];
        $data['gold_tribute']=$goldRun['gold_flag_max']>$goldRun['gold_flag']?mt_rand($goldRun['gold_flag'],$goldRun['gold_flag_max']):$goldRun['gold_flag'];
        $father_gold_tribute=$goldRun['gold_tribute_max']>$goldRun['gold_tribute']?mt_rand($goldRun['gold_tribute'],$goldRun['gold_tribute_max']):$goldRun['gold_tribute'];
        $data['father_gold_tribute']=$father_gold_tribute*$multiple;
        $data['grandfather_gold_tribute']=$goldRun['gold_grandfather_max']>$goldRun['gold_grandfather']?mt_rand($goldRun['gold_grandfather'],$goldRun['gold_grandfather_max']):$goldRun['gold_grandfather'];
        $data['status']=1;
        $data['is_del']=1;
        $day=date('Ymd');
        $data['description']=$goldRun['title'];
        $arr=($goldRunExt->validateLoginDay7($data,$goldRun));
        $outArr=array();
        $outArr['day7']=$arr;
        $outArr['islogin']=0;
        $outArr['dayGold']=10;
        $outArr['myGold']=0;
        $outArr['user_id']=$userInfo['c_user_id'];
        $day=hoursAtFmart($day);
        foreach ($arr as $key => $value) {
            if($value['hours_at']==$day){
                $outArr['islogin']=$value['status'];
                if($value['gold_tribute']){
                    $outArr['dayGold']=$value['gold_tribute']+5;
                }
            }
        }
        return out($outArr, 200, 'successed');
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
        $data=$goldRunExt->newMissionValidateBaseAll($userInfo['c_user_id'],$goldRun);

        $data['description']=$goldRun['title'];
        $data['hours_at']=date('Ymd');
        if($goldRunExt->validateLoginDay($data,$goldRun)){
            return out('', 502, 'Today has checked in');
        }

        $data['hours_at']=date('Ymd',strtotime('-1 day'));
        $row=$goldRunExt->validateLoginDay($data,$goldRun);
        if($row){
            $data['gold_tribute']= $row[0]['gold_tribute'] + $goldRun['gold_flag_max'];
        }

        if($data['gold_tribute']>40){
            $data['gold_tribute']=40;
        }
        $outArr=array();

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

        $gold=Gold::addUserGold($goldData);

        if($gold){
            $goldTomorrow=$data['gold_tribute']+ $goldRun['gold_flag_max'];
            $goldTomorrow=($goldTomorrow>40)?40:$goldTomorrow;
            $outArr=array('gold_flag'=>$data['gold_tribute'],'gold_tomorrow'=>$goldTomorrow);

            $UserTaskRecord=new UserTaskRecord();
            $a1=$UserTaskRecord->where('user_id',$userInfo['c_user_id'])->column('task_id');
            $goldRun = new GoldRun();

            if($a1){
                $rows=$goldRun->where(['id'=>array('not in',$a1),'is_activation'=>1,'tile_type'=>array('in',array(1,2))])->order('sort','desc')->field("id,user_id,title,title_gold,content,button,button_url,img_url,is_login,tile_type,key_code,type,gold_flag,gold_tribute,gold_grandfather,gold_flag_max,gold_tribute_max,gold_grandfather_max,is_activation,expire_time,activation_time")->select();
            }else{
                $rows=$goldRun->where(['is_activation'=>1,'tile_type'=>array('in',array(1,2))])->order('sort','desc')->field("id,user_id,title,title_gold,content,button,button_url,img_url,is_login,tile_type,key_code,type,gold_flag,gold_tribute,gold_grandfather,gold_flag_max,gold_tribute_max,gold_grandfather_max,is_activation,expire_time,activation_time")->select();
            }

            foreach ($rows as $row) {
                $outArr['menu'][]=$row;
            }

            $outArr['menu']['user_id']=$userInfo['c_user_id'];
            return out($outArr, 200, 'success');
        }else{
            return out('', 502, 'error');
        }

    }
}

