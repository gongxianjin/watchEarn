<?php

namespace app\app\controller;

use think\Controller;
use think\Request;
use app\app\controller\BaseController;
use app\common\MyController;
use app\model\GoldRun;
use app\model\User;
use app\model\Grade;
use app\app\library\GoldRunExt;
use app\app\library\Gold;
use app\app\library\Apprentice;
class GoldRunr extends BaseController
{
    /**
     * 显示资源列表
     *http://www.angate.cn/app/GoldRun
     * @return \think\Response
     */
    public function index()
    {
        $GoldRun = new GoldRun();
        $rows=$GoldRun->where(['is_activation'=>1,'tile_type'=>array('in',array(1,2))])->order('sort','desc')->select();
        $arr=array();
        // $arr['data']=$rows;
        foreach ($rows as $row) {
            $arr['data']['menu'.$row['tile_type']][]=$row;
        }
        $arr['tile_type']=config("active_type");
        return out($arr,"20000","success");
    }

    /**
     * 显示指定的资源 share_friend_prentice
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function costList(Request $request)
    {

        $list = Db("gold_run")->select();
        $this->assign('list', $list);

        return $this->fetch();
        // $config = Config::all(['type' => 1]);
        // $config = array_column(to_array($config), null, 'key');

        // $this->assign('config', $config);
        // return $this->fetch();
    }


    /**
     * http://hui.cn/app/GoldRun/news?id=1&user_id=1&user_father_id=2&user_grandfather_id=3
     *
     * @return \think\Response
     */
    public function news()
    {
        $request = request()->param();
        if(isset($this->user_id)){
            $request['user_id']=$this->user_id;
        }
        if(!isset($request['id']) || !isset($request['user_id']) ){
            return out(array('id','user_id'),'10002',"error");
        }
        $GoldRun = GoldRun::find($request['id']);
        if($GoldRun && !$GoldRun['is_activation']){
            return out("","10002","活动结束");
        }
        if($GoldRun && !$GoldRun['expire_time']>time()){
            return out("","10002","活动过期");
        }
        $GoldRunExt = new GoldRunExt();
        if($GoldRun && $GoldRun['num'] && !$GoldRun['sycle']){
            if($GoldRunExt->validateNum($request['id'],$request['user_id'],$GoldRun['num'])){
                return out("","10002","任务已全部完成");
            }
        }
        if($GoldRun && $GoldRun['num'] && $GoldRun['sycle']){
            if($GoldRunExt->validateNum($request['id'],$request['user_id'],$GoldRun['num'],strtotime("-".$GoldRun['sycle']." hours"))){
                return out("","10002","任务已全部完成");
            } 
        }
        $multiple=1;
        if($GoldRun['type']==1){
        /*
         * 调用传的是用户id
         * 判断成为有效徒弟
         */
            $Apprentice = new Apprentice();
            $Apprentice->changeEffectiveApprentice($request['user_id']);
            
        }elseif ($GoldRun['type']==5) {
            $user_father_id = User::where('c_user_id', $request['user_id'])->value('user_father_id');
            if($user_father_id){
             $grade_id = User::where('c_user_id', $user_father_id)->value('grade_id');
             $multiple = Grade::where('id', $grade_id)->value('multiple');
                
            }
        }elseif ($GoldRun['type']==6) {
            //得到shifu
            $user_father_id = User::where('c_user_id', $request['user_id'])->value('user_father_id');
            if($user_father_id){
                //活动时间内数 $multiple =有效徒弟个数//验证是否有相应类型的金币入库
                 $grade_id = User::where('c_user_id', $user_father_id)->value('grade_id');
                 $multiple = Grade::where('id', $grade_id)->value('multiple');
                if($GoldRunExt->validateNumTime($request['id'],$user_father_id,$multiple,$GoldRun['activation_time'],$GoldRun['expire_time'])){
                    return out("","10002","任务已全部完成");
                }

            }
        }
        $Gold = new Gold();
        $user_id=$request['user_id'];
        // $type_task_id=$request['id'];
        // $type=$GoldRun['type'];
        $type_key=$GoldRun['key_code'];
        $type_task_id=0;
        $type=$request['id'];
        $gold_tribute=$GoldRun['gold_flag_max']>$GoldRun['gold_flag']?mt_rand($GoldRun['gold_flag'],$GoldRun['gold_flag_max']):$GoldRun['gold_flag'];
        $father_gold_tribute=$GoldRun['gold_flag_max']>$GoldRun['gold_flag']?mt_rand($GoldRun['gold_flag'],$GoldRun['gold_flag_max']):$GoldRun['gold_flag'];
        $father_gold_tribute=$father_gold_tribute*$multiple;
        $grandfather_gold_tribute=$GoldRun['gold_flag_max']>$GoldRun['gold_flag']?mt_rand($GoldRun['gold_flag'],$GoldRun['gold_flag_max']):$GoldRun['gold_flag'];
    if($GoldRun['type']==1){
        Apprentice::readGetInvitationGold($user_id);
    }
    return $Gold->addUserGold($user_id, $gold_tribute, $type, $type_key,$type_task_id, $is_father_contribute = 2, $is_grandfather_contribute = 2, $father_gold_tribute , $grandfather_gold_tribute);
        // var_dump($GoldRun);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function validateRun($request)
    {
    }

}
