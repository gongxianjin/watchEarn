<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-23
 * Time: 下午4:25
 */

namespace app\app\library;

use app\app\controller\mission_new\HiretwoStudent;
use app\model\Config;
use app\model\FirstInvitationApprenticeTask;
use app\model\GoldProductRecord;
use app\model\Grade;
use app\model\TaskInvoice;
use app\model\User;
use app\model\UserApprentice;
use app\model\UserData;
use app\model\UserReadRecord;
use time\TimeUtil;
use app\model\GoldRun;

class Apprentice
{


    /**
     * 创建徒弟关系
     * @param int $user_id 填邀请码的用户id
     * @param string $invitation_code 邀请码
     * @return array
     */
    public static function createApprentice($user_id, $invitation_code)
    {   
        //判断自己信息
        //
        if($invitation_code == 66666){
            self::invitationAddGold($user_id,2);
            User::update(['user_father_id' =>0, 'user_grandfather_id' =>0, 'father_invitation_code' => $invitation_code], ['c_user_id' => $user_id]);
        }else{
            $createTime =  user::where(['c_user_id'=>$user_id])->value("create_time");
            $fatherUser = User::where([
                'invitation_code'=>$invitation_code,
               //'create_time'=>['<',$createTime],
            ])
            ->field('c_user_id,user_father_id,create_time')
            ->find();
            //为空，不存在
            if(empty($fatherUser)){
                return ['code' => 10003, 'msg' => 'invalid code,change to another one'];
            }
            if($fatherUser['create_time'] > $createTime){
                return ['code' => 10003, 'msg' => 'Your inviter can not register after  you'];
            }

            $data[] = [
                'master_user_id' => $fatherUser['c_user_id'],
                'apprentice_user_id' => $user_id,
                'is_effective' => 1,
                'type' => 1,
            ];

            $user_grandfather_id = 0;
            if (!empty($fatherUser['user_father_id'])){
                    $user_grandfather_id = $fatherUser['user_father_id'];
                    //给师祖的徒孙数加1
                    UserData::where('user_id', $user_grandfather_id)->setInc('disciple_num', 1);
                    $gandfatherData = [
                        'master_user_id' => $user_grandfather_id,
                        'apprentice_user_id' => $user_id,
                        'type' => 2,
                    ];
                    array_push($data, $gandfatherData);
            }
           
            $userApprentice = new UserApprentice();
            $userApprentice->saveAll($data);
            //增加用户的师傅师祖
            User::update(['user_father_id' => $fatherUser['c_user_id'], 'user_grandfather_id' => $user_grandfather_id, 'father_invitation_code' => $invitation_code], ['c_user_id' => $user_id]);
            //给师傅的徒弟数加1
            $userData = UserData::where('user_id', $fatherUser['c_user_id'])->field('apprentice_total')->find();
            $userData->apprentice_total = $userData->apprentice_total + 1;
            $userData->save();
             //更新师傅等级
            $Grade = new Grade();
            $gradeMsg = $Grade->userGrade($userData->apprentice_total);
            if(!empty($gradeMsg)){
                  User::update(['grade_id' => $gradeMsg['id']], ['c_user_id' => $fatherUser['c_user_id']]);
            }
            //邀请第一个徒弟额
            self::firstInvitation($fatherUser['c_user_id']);
            //输入邀请码加金币
            self::invitationAddGold($user_id);
            //判断成为有效徒弟
            //self::changeEffectiveApprentice($user_id);
        }
        return ['code' => 200, 'msg' => 'success'];
    }

    //邀请第一个徒弟额外给6000
    private static function firstInvitation($father_user_id)
    {

        $goldRunId = 1006;
        $goldRun = GoldRun::find($goldRunId);
        $res = GoldProductRecord
            ::where('type_key','eq',$goldRun['key_code'])
            ->where('user_id','eq',$father_user_id)
            ->value('id');

        if (empty($res))
        {
            $param = [
                "user_id"=>$father_user_id,
                "type"=>$goldRunId,
                "gold_tribute"=> $goldRun['gold_flag'],
                "status"=>2,//立即发放
                "type_key"=>$goldRun['key_code'],
                "title"=>$goldRun['title'],
            ];
            //输入邀请码加金币
            Gold::addUserGold($param);
        }
    }

    /*
     * 输入邀请码加金币  type  = 1  输入他人邀请码   2 官方邀请码
     */
    private static function invitationAddGold($user_id,$type=1)
    {


        //获取加入金币
        $TaskMsg = GoldRun::find(11);//查询到输入邀请码任务信息
        $param = [
            "user_id"=>$user_id,
            "type"=>$TaskMsg['id'],
            "gold_tribute"=> ($type == 1)?$TaskMsg['gold_flag']:600,
            "status"=>2,//立即发放
            "type_key"=>$TaskMsg['key_code'],
            "title"=>"Invitation code",
        ];
        //输入邀请码加金币
        Gold::addUserGold($param);
    }

    

    /*
     * 判断成为有效徒弟
     * 调用传的是用户id
     */
    public static function changeEffectiveApprentice($apprentice_user_id)
    {
        $userData = UserData::where('user_id', $apprentice_user_id)->field('read_article_gold_total,effective_apprentice_num')->find();
        //可能还需要加新手一元提现任务是否完成
        if ($userData['read_article_gold_total'] >= config('effective_apprentice_need_gold')) {
            $user_father_id = User::where('c_user_id', $apprentice_user_id)->value('user_father_id');
            if ($user_father_id){
                //判断是否已经是有效徒弟了
                $userApprentice = UserApprentice::where(['master_user_id' => $user_father_id, 'apprentice_user_id' => $apprentice_user_id])->find();
                if (!empty($userApprentice) && $userApprentice['is_effective'] == 1) {
                    //更新徒弟表状态
                    $userApprentice->is_effective = 2;
                    $userApprentice->save();
                    //更新师傅的有效徒弟数
                    $fatherUserData = UserData::where('user_id', $user_father_id)->field('effective_apprentice_num')->find();
                    $fatherUserData->effective_apprentice_num = (int)$fatherUserData->effective_apprentice_num + 1;
                    $fatherUserData->save();
                    //收徒大吉活动收有效徒弟 计算钱数
                    self::apprenticeActivity($user_father_id, $fatherUserData->effective_apprentice_num);
                    //师傅的等级判断
                    $grade_id = User::where('c_user_id', $user_father_id)->value('grade_id');
                    if ($grade_id){
                        $grade_array = Grade::order('need_apprentice_num', 'asc')->column('need_apprentice_num', 'id');
                        $need_apprentice_num = $grade_array[$grade_id];

                        $grade_array_value = array_values($grade_array);
                        $key = array_search($need_apprentice_num, $grade_array_value);
                        if(isset($grade_array_value[$key+1])){
                            $next_need_apprentice_num = $grade_array_value[$key+1];
                            if ($fatherUserData['effective_apprentice_num'] >= $next_need_apprentice_num) {
                                $new_grade_id = array_search($next_need_apprentice_num, $grade_array);
                                //更新用户等级
                                User::update(['grade_id' => $new_grade_id], ['c_user_id' => $user_father_id]);
                            }
                        }
                    }
                    else {
                        $next_grade_id = Grade::where('need_apprentice_num', '<=', $fatherUserData['effective_apprentice_num'])->order('need_apprentice_num', 'desc')->value('id');
                        //更新用户等级
                        User::update(['grade_id' => $next_grade_id], ['c_user_id' => $user_father_id]);
                    }
                }
            }
        }

        return true;
    }

    /*
     * 收徒大吉活动收有效徒弟 计算钱数
     */
    private static function apprenticeActivity($user_father_id, $effective_apprentice_num)
    {
        $data = self::getApprenticeActivityGold($effective_apprentice_num);
        Gold::addUserGold($user_father_id, $data['add_gold_tribute'], 1, 0, 'apprentice_activity', '收徒大吉活动');
    }

    /*
     * 通过徒弟个数计算用户获得的金币数
     */
    public static function getApprenticeActivityGold($apprentice_num)
    {
        $apprentice_activity = config('apprentice_activity');
        $key = 0;
        $totalMoney=0;
        foreach ($apprentice_activity as $k => $v){
            if (isset($apprentice_activity[$k + 1])){
                if($apprentice_num >= $v['apprentice_num'] && $apprentice_num < $apprentice_activity[$k + 1]['apprentice_num']){
                    $key = $k;
                    break;
                }
                elseif ($apprentice_num < $v['apprentice_num']){
                    $key = -1;
                    break;
                }
            }
            else {
                $key = $k;
                break;
            }
        }

        if ($key >= 0){
            $totalMoney = $apprentice_activity[$key]['record_money'];
        }
        if ($key >= 1){
            $addMoney = $apprentice_activity[$key]['record_money'] - $apprentice_activity[$key - 1]['record_money'];
        }
        elseif($key < 0) {
            $addMoney = $totalMoney = 0;
        }
        else {
            $addMoney = $apprentice_activity[$key]['record_money'];
        }
        //钱转化为金币
        $add_gold_tribute =  2000 * (int)$addMoney;
        //本阶段钱数
        $now_stage = $key + 1;
        $now_stage_money = $apprentice_activity[$now_stage]['record_money'];
        $stage_finish_total = $apprentice_activity[$now_stage]['apprentice_num'];
        return ['totalMoney' => $totalMoney, 'add_gold_tribute' => $add_gold_tribute, 'addMoney' => $addMoney, 'now_stage' => $now_stage+1, 'now_stage_money' => $now_stage_money, 'stage_finish_total' => $stage_finish_total, 'stage_already_finish_num' => $apprentice_num];
    }

    /*
     * 阅读获得收徒的奖励
     * 调用传的是用户id
     */
    public static function readGetInvitationGold($apprentice_user_id, $read_gold_tribute)
    {
        //记录用户每天的阅读数据
        $read_date = date('Ymd', time());
        $userReadRecord = UserReadRecord::where(['user_id' => $apprentice_user_id, 'read_date' => $read_date])->find();
        if (empty($userReadRecord)){
            $recordData = [
                'user_id' => $apprentice_user_id,
                'day_gold_total' => $read_gold_tribute,
                'is_finish_read_task' => 1,
                'read_date' => $read_date,
            ];
            $userReadRecord = new UserReadRecord($recordData);
            $userReadRecord->save();
        }
        else {
            $userReadRecord->day_gold_total = (int)$userReadRecord->day_gold_total + (int)$read_gold_tribute;
            $userReadRecord->save();
        }

        //判断用户今天是否已经完成了阅读任务
        if ($userReadRecord['is_finish_read_task'] == 1){
            $user = User::where('c_user_id', $apprentice_user_id)->field('create_time,user_father_id,user_grandfather_id')->find();
            if (!empty($user)){
                //判断注册天数是否小于7天
                $day_interval = (int)ceil((time() - (int)$user['create_time'])/(24*3600));
                if ($day_interval <= 7) {
                    //判断用户今天阅读获得的总金币数是否大于配置任务的金币数
                    if ($userReadRecord['day_gold_total'] >= config('read_how_gold_get_invitation_reward')){
                        //更新今日用户已经完成了阅读任务
                        $userReadRecord->is_finish_read_task = 2;
                        $userReadRecord->save();
                        //按注册的天数取奖励金币数配置
                        $apprentice_config = config('invitation_apprentice_read_gold');
                        $disciple_config = config('invitation_disciple_read_gold');
                        if (isset($apprentice_config[$day_interval]) && $user['user_father_id']){
                            $apprentice_gold_num = $apprentice_config[$day_interval];
                            //师傅的收徒活动奖励
                            $disciple_gold_num = $tribute_status = 0;
                            $tribute_title = '';
                            if (isset($disciple_config[$day_interval]) && $user['user_grandfather_id']) {
                                $tribute_status = 1;
                                $disciple_gold_num = $disciple_config[$day_interval];
                                $tribute_title = '收徒孙任务获得的奖励';
                            }
                            //收徒活动的奖励
                            Gold::addUserGold($user['user_father_id'], $apprentice_gold_num, 1, 0, 'invitation_apprentice', '收徒任务获得的奖励', 0, $disciple_gold_num, 0, $tribute_status, $tribute_title);
                            //新人首邀两名徒弟任务
                            if (FirstInvitationApprenticeTask::where(['master_user_id' => $user['user_father_id'], 'apprentice_user_id' => $apprentice_user_id])->count()){
                                $first_config = config('first_invitation_apprentice_read_record');
                                Gold::addUserGold($user['user_father_id'], $first_config[$day_interval], 1, 0, 'first_invitation_apprentice', '首邀请收徒任务阅读获得的奖励');
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * 完成一元提现任务
     * 新手首邀两名徒弟任务需要用到此方法
     * @param int $withdrawals_user_id 一元提现的用户id
     */
    public static function completeOneMoneyWithdrawalsTask($withdrawals_user_id)
    {
        //应该先检测用户是否已经完成了首邀2名徒弟任务

        $user_father_id = User::where('c_user_id', $withdrawals_user_id)->value('user_father_id');
        $firstInvitationApprenticeTask = FirstInvitationApprenticeTask::all(['master_user_id' => $user_father_id]);
        //检测师傅是否已经首邀了2名徒弟
        if (count($firstInvitationApprenticeTask) == 2){
            //检测提现用户是否是师傅的前2名徒弟之一
            $firstInvitationApprenticeTask = array_column(to_array($firstInvitationApprenticeTask), null, 'apprentice_user_id');
            if (in_array($withdrawals_user_id, array_keys($firstInvitationApprenticeTask))){
                //判断该用户是否完成一元提现任务
                if ($firstInvitationApprenticeTask[$withdrawals_user_id]['is_apprentice_complete_withdrawals'] == 1){
                    unset($firstInvitationApprenticeTask[$withdrawals_user_id]);
                    $other_user_id = array_keys($firstInvitationApprenticeTask)['0'];
                    if ($firstInvitationApprenticeTask[$other_user_id]['is_apprentice_complete_withdrawals'] == 2){
                        //两个徒弟完成一元提现任务加金币
                        Gold::addUserGold($user_father_id, 8000, 1, 0, 'first_invitation_apprentice', '指导两个徒弟完成一元提现任务');
                    }
                    //更新任务表
                    FirstInvitationApprenticeTask::update(['is_apprentice_complete_withdrawals' => 2], ['master_user_id' => $user_father_id, 'apprentice_user_id' => $withdrawals_user_id]);
                }
            }
        }

        return true;
    }

    /*
     * 输入邀请码
     */
    public static function inputInviteCode($user_id, $code_or_phone)
    {
        $strlen = strlen($code_or_phone);
        if($strlen === 11) {
            $invitation_code = User::where('telphone', $code_or_phone)->value('invitation_code');
            if(empty($invitation_code)){
                return ['code' => '10001', 'msg' => 'wrong phone number,change to another'];
            }
            $invitation_code = strtoupper($invitation_code);
        }else{
            $invitation_code = $code_or_phone;
            $invitation_code = strtoupper($invitation_code);
        }
        $user = User::where('c_user_id', $user_id)->field('father_invitation_code,invitation_code')->find();
        if (!empty($user['father_invitation_code'])){
            return ['code' => '10001', 'msg' => 'already be invited'];
        }
        if ($user['invitation_code'] == $invitation_code){
            return ['code' => '10001', 'msg' => 'can\'t use self invitation code '];
        }

        //创建徒弟关系
        $res = self::createApprentice($user_id, $invitation_code);
        if ($res['code'] == 200){
            //添加用户完成记录
            UserTackRecord::createRecord($user_id, 'input_share_code');
        }

        //首邀两名徒弟
//        if ($res['code']==200)
//        {
//            (new HiretwoStudent())->run([
//                'c_user_id'=>$user_id
//            ]);
//        }

        return $res; 
        
    }
}