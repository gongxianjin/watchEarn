<?php

namespace app\app\controller;

use think\Controller;
use think\Request;
use app\common\MyController;
use app\model\GoldRun;
use app\model\User;
use app\model\Grade;
use app\model\TaskInvoice;
use app\model\TaskLog;
use app\app\library\GoldRunExt;
use app\app\library\Gold;
use app\app\library\Apprentice;
use app\app\controller\BaseController;
use app\common\library\Util;
use think\Db;
use think\Cookie;
class Share extends BaseController
{
    /**
     * 分享后回调的接口
     * http://hui.cn/app/share/friend?user_id=1&task_id=8&key_code=share_friend_prentice
     * @return \think\Response
     */
    public function friend(Request $request)
    {   
        $request=$request->param();
        if(isset($this->user_id)){
            $request['user_id']=$this->user_id;
        }
        $GoldRunExt = new GoldRunExt();
        $out=$GoldRunExt->validateBaseAll($request);
        if($out['code']!=200){
            return out("","10002",$out['msg']);
        }
        $GoldRun=($out['obj']);
        $data=$out['data'];
        $GoldRun['sycle']=4;
        $outArr=array();
        $outArr['num_max']=$GoldRun['num'];
        $outArr['num']=$outArr['time_difference']=$outArr['is']=$outArr['update_time']=$outArr['gold_tribute']=$outArr['is_add_gold']=0;
        $data['hours_at']=date('Ymd');
        $rowsDay=$GoldRunExt->getInvoice($data,$GoldRun);
        $outArr['num']=count($rowsDay);
        if($outArr['num']>$outArr['num_max']){
            return out("","10002","任务已全部完成");
        }
        $data['hours_at']=date('YmdH',strtotime("-".($GoldRun['sycle'])." hours"));
        $row=$GoldRunExt->getInvoice($data,$GoldRun);
        if($row){
             $outArr['update_time']=date("Y-m-d H:i:s",($row[0]['update_time']+4*60*60));
             $outArr['time_difference']=14400-(time()-$row[0]['update_time']);
             $outArr['gold_tribute']=$row[0]['gold_tribute'];
             $outArr['is']=1;
        }else{
            //匿名执行
            $func = function () use ( &$data ,&$GoldRun ,&$GoldRunExt )
            {
                $GoldRunExt->addUserGoldExtAll($data,$GoldRun);
            };

            $gold=Gold::addUserGold($data['user_id'], $data['gold_tribute'],2, $data['task_id'], $data['key_code'],$GoldRun['title'],0,$data['father_gold_tribute'],$data['grandfather_gold_tribute'],2,$GoldRun['title'],$func);
                $outArr['gold_tribute']=$data['gold_tribute'];
                $outArr['update_time'] = date("Y-m-d H:i:s",time()+4*60*60);
                $outArr['is_add_gold']=1;
        }
        return out($outArr, 200, 'successed');
    }


    /**
     * 朋友阅读后调用的接口
     * http://hui.cn/app/share/read?user_id=1&task_id=21&key_code=share_friend_prentice_read
     * @return \think\Response
     */
    public function read(Request $request)
    {   
        if(Cookie::get('read')){
            return out("","10002","您已经阅读完成");
        }
        $request=$request->param();
        if(isset($this->user_id)){
            $request['user_id']=$this->user_id;
        }
        $GoldRunExt = new GoldRunExt();
        $out=$GoldRunExt->validateBaseAll($request);
        if($out['code']!=200){
            return out("","10002",$out['msg']);
        }
        $GoldRun=($out['obj']);
        $data=$out['data'];
        //匿名执行
        $func = function () use ( &$data ,&$GoldRun ,&$GoldRunExt )
        {
            $GoldRunExt->addUserGoldExtAll($data,$GoldRun);
        };
        $data['description']=$GoldRun['title'];
        if($GoldRunExt->validateGoldSum($data,$GoldRun)){
            return out("","10002","活动已完成");
        }
        $gold=Gold::addUserGold($data['user_id'], $data['gold_tribute'],2, $data['task_id'], $data['key_code'],$GoldRun['title'],0,$data['father_gold_tribute'],$data['grandfather_gold_tribute'],2,$GoldRun['title'],$func);
        if($gold){
            Cookie::set('read','1',36000);
            return out(array('gold_flag'=>$data['gold_tribute']), 200, 'successed');
        }else{
            return out('', 502, 'error');
        }
    }


    /**
     * 分享到朋友圈收徒落地页面数据显示接口
     *http://hui.cn/app/share/landingPage?user_id=1&task_id=8&key_code=share_friend_prentice
     * @return \think\Response
     */
    public function landingPage(Request $request)
    {
        $request=$request->param();
        if(isset($this->user_id)){
            $request['user_id']=$this->user_id;
        }
        $GoldRunExt = new GoldRunExt();
        $out=$GoldRunExt->validateBase($request);
        if($out->getData()['code']!=200){
            return $out;
        }
        $GoldRun=($out->getData()['data']);
        $data=array();
        $data['user_id']=$request['user_id'];
        $data['task_id']=$GoldRun['id'];
        $data['key_code']=$GoldRun['key_code'];
        $data['gold_tribute']=$GoldRun['gold_tribute'];
        $data['status']=1;
        $data['hours_at']=date('Ym');
        $outArr=array();
        $outArr['num']=$outArr['time_difference']=$outArr['is']=$outArr['update_time']=$outArr['gold_tribute']= $outArr['gold_tribute_num']=0;
        $outArr['num_max']=$GoldRun['num'];
        $rowMon=$GoldRunExt->readInvoiceSum($data,$GoldRun);
        if($rowMon && $rowMon['gold_tribute']){
            $outArr['gold_tribute']=getArrNum($rowMon,'gold_tribute');
            $outArr['gold_tribute_num']=getArrNum($rowMon,'num');
            // return out($arr, 502, 'error');
        }


        $data['hours_at']=date('Ymd');
        $rowsDay=$GoldRunExt->getInvoice($data,$GoldRun);
        $outArr['num']=count($rowsDay);
        if($outArr['num']>$outArr['num_max']){
            // return out("","10002","任务已全部完成");
        }
        $data['hours_at']=date('YmdH',strtotime("-".($GoldRun['sycle'])." hours"));
        $row=$GoldRunExt->getInvoice($data,$GoldRun);
        if($row){
             $outArr['update_time']=date("Y-m-d H:i:s",($row[0]['update_time']+4*60*60));
             $outArr['time_difference']=14400-(time()-$row[0]['update_time']);
             // $outArr['gold_tribute']=$row[0]['gold_tribute'];
             $outArr['is']=1;
        }
        return out($outArr, 200, 'successed');
    }

   

    /**
     * 需要唤醒徒弟数据
     *http://hui.cn/app/share/wakeUpApprentice?user_id=0
     * @return \think\Response
     */
    public function wakeUpApprentice(Request $request)
    {
        $request=$request->param();
        if(isset($this->user_id)){
            $request['user_id']=$this->user_id;
        }
        $user = new User();
        // 查询数据集
        $a1=$user->where('user_father_id',$request['user_id'])->column('c_user_id');
        $TaskInvoice = new TaskInvoice();
        $a2=$TaskInvoice->where(['user_id'=>array('in',$a1),'status'=>1,'hours_at'=>array('egt',hoursAtFmart(date('Ymd',strtotime("-1 day"))))])->group('user_id')->column('user_id');
        $arr=array_diff($a1,$a2);
        // var_dump($a1,$a2,$arr);
        if($arr){
            $list=$user->where(['c_user_id'=>array('in',$arr)])->select();
            $outArr=array();
            foreach ($list as $row) {
                $outArr[]=array('c_user_id'=>getArrVal($row,'c_user_id'),'nickname'=>getArrVal($row,'nickname'),'telphone'=>telHideFmart(getArrVal($row,'telphone')));
            }
            return out($outArr);
        }
        return out('', 10001, 'error');
    }

    /**
     * 推送收徒动态信息接口
     *http://hui.cn/app/share/pushUsersAction
     * @return \think\Response
     */
    public function pushUsersAction(Request $request)
    {
        $request=$request->param();
        if(isset($this->user_id)){
            $request['user_id']=$this->user_id;
        }
        $sql='SELECT sum(gold_tribute_total) as gold_flag,nickname,count(apprentice_user_id) num FROM hs_user_apprentice left join hs_user on hs_user_apprentice.master_user_id = hs_user.c_user_id where type = 1 and hs_user_apprentice.create_time > ? group by master_user_id order by hs_user_apprentice.create_time desc LIMIT 0 , 30 ;';
        $rows=Db::query($sql,[strtotime('-10 day')]);

        return out($rows, 200, 'successed');
    }


    /**
     * 朋友阅读后调用的接口
     * http://hui.cn/app/share/read?user_id=1&task_id=21&key_code=share_friend_prentice_read
     * @return \think\Response
     */
    public function readValidate(Request $request)
    {   

        // Cookie::set('read','1',360000);
        //         var_dump(getClientIP());
        // $data['session_ip'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        // $data['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        // var_dump($data);

    }

}
