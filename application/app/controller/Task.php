<?php

namespace app\app\controller;

use think\Controller;
use think\Db;
use think\Request;
use app\common\MyController;
use app\model\GoldRun;
use app\model\User;
use app\model\Grade;
use app\model\TaskInvoice;
use app\model\TaskLog;
use app\model\UserTaskRecord;
use app\app\library\GoldRunExt;
use app\app\library\Gold;
use app\app\library\Apprentice;
use app\app\controller\BaseController;
class Task extends BaseController
{

    /**
     * 显示资源列表
     *http://www.angate.cn/task/index
     * @return \think\Response
     */
    public function index(Request $request)
    {   
        $request=$request->param();
        if(isset($this->user_id)){
            $request['user_id']=$this->user_id;
        }
        $UserTaskRecord=new UserTaskRecord();
        $a1=$UserTaskRecord->where('user_id',$request['user_id'])->column('task_id');
        $GoldRun = new GoldRun();
        $map['is_activation'] = 1;
        $map['tile_type'] = ['in',[1,2]];
        if($a1){
            $map['id'] = ['not in',$a1];
        }
        //查询数据
        $rows=$GoldRun->where($map)->order('sort','desc')->field("id,title,title_gold,title_gold_type,content,button,button_url,button_type,is_login,tile_type,key_code,is_activation,expire_time,activation_time")->select();
        $arr=array();
        foreach ($rows as $row) {
            $arr['data']['menu'.$row['tile_type']][]=$row;
        }

        $arr['sign']=[];
        $arr['chest']=[];
        $arr['total_gold']=0;
        if(isset($this->user_id) && isset($this->userInfo)){
            $arr['total_gold']=$this->userInfo['gold_flag'];
        }
        $request["key_code"]="task_login" ;
        $GoldRunExt = new GoldRunExt();
        $out=$GoldRunExt->validateBaseAll($request);
        if($out['code']!=200){
            // return out("","10002",$out['msg']);
        }
        $GoldRun=($out['obj']);
        $data=$out['data'];
        $outArr=array();
        $outArr['islogin']=0;
        $alertDate=$data['hours_at']=date('Ymd');
        if($GoldRunExt->validateLoginDay($data,$GoldRun)){
            $outArr['islogin']=1;
            $alertDate=date('Ymd',strtotime("+1 day"));
        }
        $outArr['day7']=($GoldRunExt->validateLoginDay7($data,$GoldRun));
        $outArr['dayGold']=10;
        $day=date('Ymd');
        $day=hoursAtFmart($day);
        foreach ($outArr['day7'] as $key => $value) {
            if($value['hours_at']==$day){
                //$outArr['islogin']=$value['status'];
                if($outArr['islogin'] == 1){
                    $outArr['day7'][$key]['status']=1;
                }else{
                    $outArr['day7'][$key]['status']=2;
                }
                if($value['gold_tribute'] ){
                    $outArr['dayGold']=$value['gold_tribute']+5;
                }
            }
        }
        //如果7天已签满则添加一个第八天的数据
        if ( $outArr['day7'][6]['status'] == 1 )
        {
            array_splice($outArr['day7'],0,1);

            //修复数据
            foreach ($outArr['day7'] as &$item)
            {
                $item['d'] += 1;
            }

            $outArr['day7'][] = [
                'hours_at'=> date("Ymd",strtotime(substr($day,0,8))+24*3600)."0"
                ,'gold_tribute'=>40
                ,'status'=>0
                ,'d'=>0
                ,'day'=>8
            ];
            $outArr['dayGold']= 40;

        }

        $arr['sign']=$outArr;

        $outArr=array();
        $request["key_code"]="treasure" ;
        $GoldRunExt = new GoldRunExt();
        $out=$GoldRunExt->validateBaseAll($request);
        if($out['code']!=200){
            // return out("","10002",$out['msg']);
        }
        $GoldRun=($out['obj']);
        $data=$out['data'];

        $data['min_time']=strtotime("-".($GoldRun['sycle'])." hours");
        $sql="SELECT hours_at,gold_tribute,update_time FROM hs_task_invoice where user_id='".$data['user_id']."' and task_id='".$data['task_id']."' and update_time>='".$data['min_time']."' and status=1  order by hours_at desc limit 1;";
        $data['hours_at']=date('YmdH',strtotime("-".($GoldRun['sycle'])." hours"));
        $row=Db::query($sql);
        $outArr=array();
        $outArr['is']=$outArr['update_time']=$outArr['gold_tribute']=0;
        if($row){
            $outArr['update_time']= date('Y-m-d H:i:s',($row[0]['update_time']+4*60*60));
            //$outArr['time_difference']=14400-(time()-$row[0]['update_time']);
            $outArr['time_difference']=14400+$row[0]['update_time']-time();
            $outArr['is']=1;
           
        }
        $arr['chest']=$outArr;
        $arr['login_flag'] = $this->login_flag;
        $facebooklinke = [
            "id" => 0,
            "title"=> "Like us on Facebook  +50 coins",
            "title_gold" => "50",
            "title_gold_type" => 0,
            "content" => "Like us on Facebook",
            "button" => "Click to watch",
            "button_url" => "https://www.facebook.com/WatchnEarnOfficial/",
            "button_type" => 1,
            "is_login" => 0,
            "tile_type" => 1,
            "key_code" => "Like us on Facebook",
            "is_activation"=>1
        ];
        array_unshift($arr['data']['menu2'],$facebooklinke);

        return out($arr);
    }

    /**
     * 签到得金币接口
     * http://hui.cn/app/task/login?user_id=1&task_id=22&key_code=task_login
     * @return \think\Response
     */
    public function login(Request $request)
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
        $type_task_id=0;
          
        $data['description']=$GoldRun['title'];
        $data['hours_at']=date('Ymd');
        if($GoldRunExt->validateLoginDay($data,$GoldRun)){
            return out('', 502, '今日已签到');
        }

        $data['hours_at']=date('Ymd',strtotime('-1 day'));
        $row=$GoldRunExt->validateLoginDay($data,$GoldRun);
        if($row){
            $data['gold_tribute']= $row[0]['gold_tribute'] + $GoldRun['gold_flag_max'];
        }
        $arr=($GoldRunExt->validateLoginDay7($data,$GoldRun));

        if($data['gold_tribute']>40){
            $data['gold_tribute']=40;
        }
        $outArr=array();
        //匿名执行
        $func = function () use ( &$data ,&$GoldRun ,&$GoldRunExt )
        {
            $GoldRunExt->addUserGoldExtAll($data,$GoldRun);
        };

        $gold=Gold::addUserGold($data['user_id'], $data['gold_tribute'],2, $data['task_id'], $data['key_code'],$GoldRun['title'],0,$data['father_gold_tribute'],$data['grandfather_gold_tribute'],2,$GoldRun['title'],$func);
        if($gold){
            $goldTomorrow=$data['gold_tribute']+ $GoldRun['gold_flag_max'];
            $goldTomorrow=($goldTomorrow>40)?40:$goldTomorrow;
            $outArr=array('gold_flag'=>$data['gold_tribute'],'gold_tomorrow'=>$goldTomorrow);


            $UserTaskRecord=new UserTaskRecord();
            $a1=$UserTaskRecord->where('user_id',$request['user_id'])->column('task_id');
            $GoldRun = new GoldRun();
            if($a1){
                $rows=$GoldRun->where(['id'=>array('not in',$a1),'is_activation'=>1,'tile_type'=>array('in',array(1,2))])->order('sort','desc')->field("id,user_id,title,title_gold,content,button,button_url,img_url,is_login,tile_type,key_code,type,gold_flag,gold_tribute,gold_grandfather,gold_flag_max,gold_tribute_max,gold_grandfather_max,is_activation,expire_time,activation_time")->select();
            }else{
                $rows=$GoldRun->where(['is_activation'=>1,'tile_type'=>array('in',array(1,2))])->order('sort','desc')->field("id,user_id,title,title_gold,content,button,button_url,img_url,is_login,tile_type,key_code,type,gold_flag,gold_tribute,gold_grandfather,gold_flag_max,gold_tribute_max,gold_grandfather_max,is_activation,expire_time,activation_time")->select();
            }
            foreach ($rows as $row) {
                $outArr['menu'][]=$row;
            }
            // $rand1=mt_rand(0,(count($rows)-1));
            // // $arr['data']['menu'.$rows[$rand1]['tile_type']][]=$rows[$rand1];
            // $outArr['menu'][]=$rows[$rand1];
            // $rand1=mt_rand(0,(count($rows)-1));
            $outArr['menu']['user_id']=$request['user_id'];
            return out($outArr, 200, 'successed');
        }else{
            return out('', 502, 'error');
        }
    }


    /**
     * 七天签到记录接口
     * http://hui.cn/app/task/loginLog?user_id=1&task_id=22&key_code=task_login
     * @return \think\Response
     */
    public function loginLog(Request $request)
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
        $multiple=1;
        $type_task_id=0;
        
        $data=array();
        $data['user_id']=$request['user_id'];
        $data['task_id']=$GoldRun['id'];
        $data['key_code']=$GoldRun['key_code'];
        $data['type']=$GoldRun['type'];
        $data['gold_tribute']=$GoldRun['gold_flag_max']>$GoldRun['gold_flag']?mt_rand($GoldRun['gold_flag'],$GoldRun['gold_flag_max']):$GoldRun['gold_flag'];
        $father_gold_tribute=$GoldRun['gold_tribute_max']>$GoldRun['gold_tribute']?mt_rand($GoldRun['gold_tribute'],$GoldRun['gold_tribute_max']):$GoldRun['gold_tribute'];
        $data['father_gold_tribute']=$father_gold_tribute*$multiple;
        $data['grandfather_gold_tribute']=$GoldRun['gold_grandfather_max']>$GoldRun['gold_grandfather']?mt_rand($GoldRun['gold_grandfather'],$GoldRun['gold_grandfather_max']):$GoldRun['gold_grandfather'];
        $data['status']=1;    
        $data['is_del']=1;
        $day=date('Ymd');    
        $data['description']=$GoldRun['title'];
        $arr=($GoldRunExt->validateLoginDay7($data,$GoldRun));
        $outArr=array();
        $outArr['day7']=$arr;
        $outArr['islogin']=0;
        $outArr['dayGold']=10;
        $outArr['myGold']=0;
        $outArr['user_id']=$request['user_id'];
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
     * 宝箱
     * http://hui.cn/app/task/treasure?user_id=1&task_id=23&key_code=treasure
     * @return \think\Response
     */
    public function treasure(Request $request)
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

        $multiple=1;
        $type_task_id=0;
        
        $data=array();
        $data['user_id']=$request['user_id'];
        $data['task_id']=$GoldRun['id'];
        $data['key_code']=$GoldRun['key_code'];
        $data['type']=$GoldRun['type'];
        $data['gold_tribute']=$GoldRun['gold_flag_max']>$GoldRun['gold_flag']?mt_rand($GoldRun['gold_flag'],$GoldRun['gold_flag_max']):$GoldRun['gold_flag'];
        $father_gold_tribute=$GoldRun['gold_tribute_max']>$GoldRun['gold_tribute']?mt_rand($GoldRun['gold_tribute'],$GoldRun['gold_tribute_max']):$GoldRun['gold_tribute'];
        $data['father_gold_tribute']=$father_gold_tribute*$multiple;
        $data['grandfather_gold_tribute']=$GoldRun['gold_grandfather_max']>$GoldRun['gold_grandfather']?mt_rand($GoldRun['gold_grandfather'],$GoldRun['gold_grandfather_max']):$GoldRun['gold_grandfather'];
        $data['status']=1;    
        $data['is_del']=1;
        $day=date('Ymd');    
        $data['description']=$GoldRun['title'];
        $data['hours_at']=date('YmdH',strtotime("-".($GoldRun['sycle'])." hours"));
        $row=$GoldRunExt->getInvoice($data,$GoldRun);
        $outArr=array();
        $outArr['is']=$outArr['update_time']=$outArr['gold_tribute']=0;
        if($row){
             $outArr['update_time']=date("Y-m-d H:i:s",($row[0]['update_time']+4*60*60));
             $outArr['time_difference']=14400-(time()-$row[0]['update_time']);
             $outArr['is']=1;
        }else{
            //匿名执行
            $func = function () use ( &$data ,&$GoldRun ,&$GoldRunExt )
            {
                $GoldRunExt->addUserGoldExtAll($data,$GoldRun);
            };

            $gold=Gold::addUserGold($data['user_id'], $data['gold_tribute'],2, $data['task_id'], $data['key_code'],$GoldRun['title'],0,$data['father_gold_tribute'],$data['grandfather_gold_tribute'],2,$GoldRun['title'],$func);
                $outArr['gold_tribute']=$data['gold_tribute'];
                $outArr['is'] = 1;
                $outArr['update_time'] = date("Y-m-d H:i:s",time()+4*60*60);
                $outArr['time_difference']=14400;
        }
        return out($outArr, 200, 'successed');
    }

    /**
     * http://hui.cn/app/task/news??user_id=1&task_id=3&key_code=share_friend_prentice
     *
     * @return \think\Response
     */
    public function news(Request $request)
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
        $multiple=1;
        $type_task_id=0;
        $father_title='';
        if($GoldRun['type']==1){

            if($GoldRun && $GoldRun['num'] && !$GoldRun['sycle']){
                $dataSum=$data;
                $dataSum['hours_at']='2018020100';
                $invoice=$GoldRunExt->sumInvoice($dataSum);
                if(getArrNum($invoice,'code')==200){
                    if(getArrNum(getArrVal($invoice,'data'),'num')>$GoldRun['num']){
                        return out("","10002","任务已全部完成");
                    }
                } 
            }elseif($GoldRun && $GoldRun['num'] && $GoldRun['sycle']){
                $dataSum=$data;
                if($GoldRun['sycle']==24){
                    $dataSum['hours_at']=date('Ymd',strtotime("-".$GoldRun['sycle']." hours"));
                }else{
                    $dataSum['hours_at']=date('YmdH',strtotime("-".$GoldRun['sycle']." hours"));
                }
                $invoice=$GoldRunExt->sumInvoice($dataSum);
                if(getArrNum($invoice,'code')==200){
                    if(getArrNum(getArrVal($invoice,'data'),'num')>$GoldRun['num']){
                        return out("","10002","任务已全部完成");
                    }
                }
            }

            //特权师傅4~7倍奖励    
            $user_father_id = User::where('c_user_id', $request['user_id'])->value('user_father_id');
            if($user_father_id){
             $grade_id = User::where('c_user_id', $user_father_id)->value('grade_id');
             if($grade_id){
                $multiple = Grade::where('id', $grade_id)->value('multiple');
             }
            }
            $data['father_gold_tribute']=$data['gold_tribute']*$multiple;
            if(isset($this->userInfo)){
                $father_title=getArrVal($this->userInfo,'nickname') . $GoldRun['title'];
            }
        }elseif ($GoldRun['type']==5) {
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
        //匿名执行
        $func = function () use ( &$data ,&$GoldRun ,&$GoldRunExt )
        {
            $GoldRunExt->addUserGoldExtAll($data,$GoldRun);
        };
        var_dump($data);exit;
        $gold=Gold::addUserGold($data['user_id'], $data['gold_tribute'],2, $data['task_id'], $data['key_code'],$GoldRun['title'],0,$data['father_gold_tribute'],$data['grandfather_gold_tribute'],1,$father_title,$func);
        if($gold){
            return out(array('gold_flag'=>$data['gold_tribute']), 200, 'successed');
        }else{
            return out('', 502, 'error');
        }
    }


    /**
     * http://hui.cn/app/task/reading?user_id=1
     *阅读总接口

     * @return \think\Response
     */
    public function reading(Request $request)
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
        //匿名执行
        $func = function () use ( &$data ,&$GoldRun ,&$GoldRunExt )
        {
            $GoldRunExt->addUserGoldExtAll($data,$GoldRun);
        };

        $gold=Gold::addUserGold($data['user_id'], $data['gold_tribute'],2, $data['task_id'], $data['key_code'],$GoldRun['title'],0,$data['father_gold_tribute'],$data['grandfather_gold_tribute'],2,$GoldRun['title'],$func);
        if($gold){
            return out(array('gold_flag'=>$data['gold_tribute']), 200, 'successed');
        }else{
            return out('', 502, 'error');
        }
    }
    /**
     * validate
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function validateRun(Request $request)
    {
        $request=$request->param();
        if(isset($this->user_id)){
            $request['user_id']=$this->user_id;
        }
        $GoldRunExt = new GoldRunExt();

        $data=$request;
        $data['hours_at']=date('Ymd');

        $data=$GoldRunExt->sumInvoice($data);

        var_dump($data);

    }
}
