<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-23
 * Time: 下午1:40
 */

namespace app\app\library;

use app\model\GoldPassiveProductRecord;
use app\model\GoldProductRecord;
use app\model\User;
use app\model\UserApprentice;
use app\model\GoldRun;
use app\model\TaskLog;
use app\model\TaskInvoice;
use app\model\UserData;
use app\app\library\Apprentice;
use app\app\library\UserTackRecord;
use app\app\library\Gold;
use think\Db;
use think\Request;
class GoldRunExt
{
    public function validateNum($id,$uid,$num,$time=0)
    {
    	$goldProductRecord = new GoldProductRecord();
    	if($time){
    		$row=$goldProductRecord->where(['type_task_id'=>$id,'user_id'=>$uid,'create_time'=>array('>',$time)])->count();
    	}else{
    		$row=$goldProductRecord->where(['type_task_id'=>$id,'user_id'=>$uid])->count();
    	}
    	if($row>$num){
			return false;
    	}else{
    		return true;
    	}
    }
    public function validateNumTime($id,$uid,$num,$start,$end)
    {
        $goldProductRecord = new GoldProductRecord();
        if($time){
            $row=$goldProductRecord->where(['type_task_id'=>$id,'user_id'=>$uid,'create_time'=>array('>',$time)])->count();
        }else{
            $row=$goldProductRecord->where(['type_task_id'=>$id,'user_id'=>$uid])->count();
        }
        if($row>$num){
            return false;
        }else{
            return true;
        }
    }
    public function validateBaseAll($request)
    {
        if(empty($request['user_id']) ){
            return ['code' => 13000, 'msg' => 'user_id不存在'];
        }
        if(empty($request['task_id']) && empty($request['key_code']) ){
            return ['code' => 13000, 'msg' => 'task_id||key_code不存在'];
        }
        if(!empty($request['task_id'])){
            $GoldRun = GoldRun::find($request['task_id']);
        }else{
            $GoldRun = new GoldRun();
            $GoldRun = $GoldRun->where(['key_code'=>$request['key_code']])->find();
        }
        if(!$GoldRun){
            return ['code' => 13000, 'msg' => '活动不存在'];
        }
        if(!$GoldRun['is_activation']){
            return ['code' => 13000, 'msg' => '活动结束'];
        }
        if(!$GoldRun['expire_time']>time()){
            return ['code' => 13000, 'msg' => '活动过期'];
        }
        $data=array();
        $data['user_id']=$request['user_id'];
        $data['task_id']=$GoldRun['id'];
        $data['key_code']=$GoldRun['key_code'];
        $data['type']=$GoldRun['type'];

        $data['gold_tribute']=$GoldRun['gold_flag_max']>$GoldRun['gold_flag']?mt_rand($GoldRun['gold_flag'],$GoldRun['gold_flag_max']):$GoldRun['gold_flag'];
        $data['father_gold_tribute']=$GoldRun['gold_tribute_max']>$GoldRun['gold_tribute']?mt_rand($GoldRun['gold_tribute'],$GoldRun['gold_tribute_max']):$GoldRun['gold_tribute'];
        $data['grandfather_gold_tribute']=$GoldRun['gold_grandfather_max']>$GoldRun['gold_grandfather']?mt_rand($GoldRun['gold_grandfather'],$GoldRun['gold_grandfather_max']):$GoldRun['gold_grandfather'];
        $data['status']=$data['is_del']=1;

        // $data['is_father_contribute'] = $data['father_gold_tribute']>0?2:1;
        // $data['is_grandfather_contribute'] = $data['father_gold_tribute']>0?2:1;
        return ['code' => 200, 'msg' => 'success','data'=>$data,'obj'=>$GoldRun];
    }

    public function newMissionValidateBaseAll($userId,$GoldRun)
    {
        $data=array();
        $data['user_id']=$userId;
        $data['task_id']=$GoldRun['id'];
        $data['key_code']=$GoldRun['key_code'];
        $data['type']=$GoldRun['type'];

        $data['gold_tribute']=$GoldRun['gold_flag_max']>$GoldRun['gold_flag']?mt_rand($GoldRun['gold_flag'],$GoldRun['gold_flag_max']):$GoldRun['gold_flag'];
        $data['father_gold_tribute']=$GoldRun['gold_tribute_max']>$GoldRun['gold_tribute']?mt_rand($GoldRun['gold_tribute'],$GoldRun['gold_tribute_max']):$GoldRun['gold_tribute'];
        $data['grandfather_gold_tribute']=$GoldRun['gold_grandfather_max']>$GoldRun['gold_grandfather']?mt_rand($GoldRun['gold_grandfather'],$GoldRun['gold_grandfather_max']):$GoldRun['gold_grandfather'];
        $data['status']=$data['is_del']=1;

        // $data['is_father_contribute'] = $data['father_gold_tribute']>0?2:1;
        // $data['is_grandfather_contribute'] = $data['father_gold_tribute']>0?2:1;
        return $data;
    }

    /**
     * 统一加金币扩展方法
     * @param int $user_id 用户id
     * @param string key_code 任务或者活动key_code 
     * @return array
     */
    public function addUserGoldExtAll($data,$GoldRun)
    {
        UserTackRecord::createRecord($data['user_id'],$GoldRun['key_code']);
        if($GoldRun['type']==1){
            UserData::where('user_id', $data['user_id'])->setInc('read_article_total', 1);
            UserData::where('user_id', $data['user_id'])->setInc('read_article_gold_total', $data['gold_tribute']);

        }
        if($this->addTaskLogInvoice($data,$GoldRun)){
            return ['code'=>-1,"msg"=>"记录失败"];
        }
        return ['code'=>200,"msg"=>"success"];
    }


    public function validateBase($request)
    {
        // var_dump();
        if(empty($request['user_id']) ){
            return out(array('user_id'=>''),'10002',"error");
        }
        if(empty($request['task_id']) && empty($request['key_code']) ){
            return out(array('task_id','key_code'),'10002',"error");
        }
        if(!empty($request['task_id'])){
            $GoldRun = GoldRun::find($request['task_id']);
        }else{
            $GoldRun = new GoldRun();
            $GoldRun = $GoldRun->where(['key_code'=>$request['key_code']])->find();
        }
        if(!$GoldRun){
            return out("","10002","活动不存在");
        }
        if(!$GoldRun['is_activation']){
            return out("","10002","活动结束");
        }
        if(!$GoldRun['expire_time']>time()){
            return out("","10002","活动过期");
        }
        return out($GoldRun,200,'');
    }



    /*
     * 添加金币产生表LOG
     */
    public function addTaskLogInvoice($data=array(),$GoldRun)
    {

        
        $TaskLogArr=$data;
        unset($TaskLogArr['hours_at']);
        try {
            $TaskLog = new TaskLog($TaskLogArr);
            $TaskLog->allowField(true)->save();
            if($GoldRun['sycle']==24){
                $data['hours_at']=date('Ymd');
            }else{
                $data['hours_at']=date('YmdH');
            }
            $data['hours_at']=hoursAtFmart($data['hours_at']);
            $data['sycle']=$GoldRun['sycle'];
            $data['status']=1;
            $data['num']=1;
            $TaskInvoice = new TaskInvoice();
            $row=$TaskInvoice->where(['user_id'=>$data['user_id'],'task_id'=>$data['task_id'],'hours_at'=>$data['hours_at'],'status'=>$data['status']])->find();
            if($row){
                $data['gold_tribute']=$data['gold_tribute']+$row['gold_tribute'];
                $data['father_gold_tribute']=$data['father_gold_tribute']+$row['father_gold_tribute'];
                $data['grandfather_gold_tribute']=$data['grandfather_gold_tribute']+$row['grandfather_gold_tribute'];
                $data['num']=$row['num']+1;
                $TaskInvoice->allowField(true)->save($data,['id' => $row['id']]);
            }else{
                $TaskInvoice->allowField(true)->save($data);
            }
            return true;
            // return out('', 200, '');
        } catch (Exception $e) {
            // return out('', 505, $e->getMessage());
            return false;
        }
    }
    /*
     * 添加金币产生表LOG
     */
    public function validateGoldSum($data,$GoldRun)
    {
        $data['hours_at']=date('YmdH',strtotime("-".$GoldRun['sycle']." hours"));
        $data['hours_at']=hoursAtFmart($data['hours_at']);

        $sql="SELECT sum(gold_tribute) gold_tribute FROM hs_task_invoice where user_id='".$data['user_id']."' and task_id='".$data['task_id']."' and hours_at>='".$data['hours_at']."' and status=1;";
        $row=Db::query($sql);
        if(!empty($row[0]['gold_tribute'])){
            if($row[0]['gold_tribute']>=$GoldRun['gold_flag_max']){
                return true;
            }
        }
        return false;
    }
    /*
     * 分享到朋友圈收徒落地页面数据显示接口
     */
    public function validateGoldLandingPage($data,$GoldRun)
    {
        $data['hours_at']=date('Ymd');
        $data['hours_at']=hoursAtFmart($data['hours_at']);
        $sql="SELECT  num, gold_tribute,update_time FROM hs_task_invoice where user_id='".$data['user_id']."' and task_id='".$data['task_id']."' and hours_at>='".$data['hours_at']."' and status=1 order by update_time desc;";
        return Db::query($sql);
        // if(!empty($row[0])){
        //         return $row[0];
        // }
        // return 0;
    }
    /*
     * 签到
     */
    public function validateLoginDay($data,$GoldRun)
    {
        $data['hours_at']=hoursAtFmart($data['hours_at']);
        $sql="SELECT hours_at,gold_tribute FROM hs_task_invoice where user_id='".$data['user_id']."' and task_id='".$data['task_id']."' and hours_at >='".$data['hours_at']."' and status=1;";
        return (Db::query($sql));
    }
    /*
     * 签到七天签到记录接口
     */
    public function validateLoginDay7($data,$GoldRun)
    {
        $day=date('Ymd');
        $day=hoursAtFmart($day);
        $sql="SELECT hours_at,gold_tribute FROM hs_task_invoice where user_id='".$data['user_id']."' and task_id='".$data['task_id']."' and hours_at<='".$day."' and status=1  order by hours_at desc limit 0,7;";

        $row=Db::query($sql);
        $arr=array();
        $gold_tribute=10;
        $isDay=0;
        if($row){
            foreach ($row as $key => $value) {
                if($value['hours_at']==$day){
                    $isDay=1;
                }
                break;
            }
            foreach ($row as $key => $value) {
                if($isDay){
                   $numDya=$key;
                }else{
                    $numDya=$key+1;
                }
                $alertDate=date('Ymd',strtotime("-".($numDya)." day"));
                $alertDate=hoursAtFmart($alertDate);
                if($value['hours_at']==$alertDate){
                    if($value['gold_tribute']>$gold_tribute){
                        $gold_tribute=$value['gold_tribute'];
                        if($gold_tribute>40){
                            $gold_tribute=40;
                        }
                    }
                    $arr[]=array('hours_at'=>$value['hours_at'],'gold_tribute'=>$value['gold_tribute'],'status'=>1,'d'=>$numDya);
                }else{
                    unset($row[$key]);
                }
            }
        }
        $n=count($arr);
        if($n){
            if($n<7){
                for ($i=$n; $i < 7; $i++) { 
                    if($isDay){
                        $alertDate=date('Ymd',strtotime("+".($i-$n+1)." day"));
                        $alertDate=hoursAtFmart($alertDate);

                        $gold_tributeDay=$gold_tribute+($i-$n+1)*5;
                        if($gold_tributeDay>40){
                            $gold_tributeDay=40;
                        }

                         $arr[]=array('hours_at'=>$alertDate,'gold_tribute'=>$gold_tributeDay,'status'=>0);
                    }else{
                        $alertDate=date('Ymd',strtotime("+".($i-$n)." day"));
                        $alertDate=hoursAtFmart($alertDate);
                        $gold_tributeDay=$gold_tribute+($i-$n+1)*5;
                        if($gold_tributeDay>40){
                            $gold_tributeDay=40;
                        }
                         $arr[]=array('hours_at'=>$alertDate,'gold_tribute'=>$gold_tributeDay,'status'=>0);
                    }
                }
            }
        }else{
                for ($i=$n; $i < 7; $i++) {
                    $alertDate=date('Ymd',strtotime("+".($i-$n)." day"));
                    $alertDate=hoursAtFmart($alertDate);
                        $gold_tributeDay=$gold_tribute+($i-$n)*5;
                        if($gold_tributeDay>40){
                            $gold_tributeDay=40;
                        }
                    $arr[]=array('hours_at'=>$alertDate,'gold_tribute'=>$gold_tributeDay,'status'=>0);
                }
        }
        array_multisort(array_column($arr,'hours_at'),SORT_ASC,$arr);
        $i=1;
        foreach ($arr as $key => $value) {
            $arr[$key]['day']=$i;
            $i++;
        }
        return ($arr);
    }
    /**
     * 统一加金币扩展方法
     * @param int $user_id 用户id
     * @param string key_code 任务或者活动key_code 
     * @return array
     */
    public function addUserGoldExt($data,$GoldRun)
    {
        $multiple=1;
        $type_task_id=0;
        $Gold = new Gold();
        $is_father_contribute = $data['father_gold_tribute']>0?2:1;
        $is_grandfather_contribute = $data['father_gold_tribute']>0?2:1;
        $out=$Gold->addUserGold($data['user_id'], $data['gold_tribute'],2, $data['task_id'], $data['key_code'],$type_task_id, $is_father_contribute,$is_grandfather_contribute, $data['father_gold_tribute'] , $data['grandfather_gold_tribute']);
        if($out){
            UserTackRecord::createRecord($data['user_id'],$GoldRun['key_code']);
            if($GoldRun['type']==1){
                $Apprentice = new Apprentice();
                $Apprentice->changeEffectiveApprentice($data['user_id']);
                $Apprentice->readGetInvitationGold($data['user_id'], $data['gold_tribute']);
            }
            if($this->addTaskLogInvoice($data,$GoldRun)){
            }

            return true;
        }
        return false;  
    }
    /*
     * 签到七天签到记录接口
     */
    public function getInvoice($data,$GoldRun)
    {
        $data['hours_at']=hoursAtFmart($data['hours_at']);
        $sql="SELECT hours_at,gold_tribute,update_time FROM hs_task_invoice where user_id='".$data['user_id']."' and task_id='".$data['task_id']."' and hours_at>='".$data['hours_at']."' and status=1  order by hours_at desc limit 0,6;";
        return Db::query($sql);
    }

    /*
     * 阅读类型总数据统计接口
     */
    public function sumInvoice($data)
    {
        if(empty($data['user_id'])){
            return ['code'=>-1,"msg"=>"user_id"];
        }
        if(empty($data['hours_at'])){
            return ['code'=>-1,"msg"=>"hours_at"];
        }else{
            $data['hours_at']=hoursAtFmart($data['hours_at']);
        }
        if(!empty($data['task_id'])){
            $sql="SELECT sum(num) num,sum(gold_tribute) gold_tribute,update_time FROM hs_task_invoice where user_id='".$data['user_id']."' and task_id='".$data['task_id']."' and hours_at>='".$data['hours_at']."' and status=1 order by update_time desc;";
            
        }
        if(!empty($data['type'])){
            $sql="SELECT sum(num) num,sum(gold_tribute) gold_tribute,update_time FROM hs_task_invoice where user_id='".$data['user_id']."' and type='".$data['type']."' and hours_at>='".$data['hours_at']."' and status=1 order by update_time desc;";
        }
        $row=Db::query($sql);
        if(!empty($row[0])){
            return ['code'=>200,"msg"=>"success",'data'=>$row[0]];
        }
        return ['code'=>-1,"msg"=>"失败"];
    }

    /*
     * 分享到朋友圈read数据显示
     */
    public function readInvoiceSum($data= array(),$GoldRun=null)
    {
        $data['task_id']=21;
        // $data['hours_at']=date('Ymd');
        $data['hours_at']=hoursAtFmart($data['hours_at']);
        $sql="SELECT sum(num) num,sum(gold_tribute) gold_tribute FROM hs_task_invoice where user_id='".$data['user_id']."' and task_id='".$data['task_id']."' and hours_at>='".$data['hours_at']."' and status=1";
        // $sql="SELECT sum(num) num,sum(gold_tribute) gold_tribute FROM hs_task_invoice";
        $row=Db::query($sql);
        if(!empty($row[0])){
            return $row[0];
        }
        return 0;
    }
    /*
     * 今日分享统计
     */
    public function dayInvoice($data)
    {
        if(empty($data['user_id'])){
            return ['code'=>-1,"msg"=>"user_id"];
        }
        if(empty($data['hours_at'])){
            return ['code'=>-1,"msg"=>"hours_at"];
        }else{
            $data['hours_at']=hoursAtFmart($data['hours_at']);
        }
        // $sql='SELECT DATE_FORMAT(left(hours_at, 8) ,"%Y-%m-%d") hours_day,sum(num) num,sum(gold_tribute) gold_tribute,user_idwhere hours_at>='.hoursAtFmart(date('Ymd')).' group by hours_day,user_id order by hours_day desc;';
        $sql="SELECT num,gold_tribute,update_time FROM hs_task_invoice where user_id='".$data['user_id']."' and task_id='".$data['task_id']."' and hours_at>='".$data['hours_at']."' and status=1 order by update_time desc;";
        $row=Db::query($sql);
        if(!empty($row[0])){
            return ['code'=>200,"msg"=>"success",'data'=>$row[0]];
        }
        return ['code'=>-1,"msg"=>"失败"];
    }


    public function validateBaseAllInvoice($request,$GoldRun)
    {
        return ['code' => 200, 'msg' => 'success','data'=>$data,'obj'=>$GoldRun];
    }

        


}
            
        