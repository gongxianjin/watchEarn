<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-23
 * Time: 下午5:13
 */

namespace app\app\controller;

use app\app\library\Apprentice as Apprenticelib ;
use app\app\library\Gold;
use app\common\library\Util;
use app\model\GoldProductRecord;
use app\model\Grade;
use app\model\User;
use app\model\UserApprentice;
use app\model\UserData;
use think\Request;
use app\app\library\UserTackRecord;

class Apprentice extends BaseController
{
    /*
     * 填写邀请码
     */
    public function inputInviteCode(Request $request)
    {
        $req = $request->param();
        $this->validate($req, [
            'code_or_phone' => 'require',
        ], [
            'code_or_phone.require' => '邀请码或手机号不能为空',
        ]);

        $res = Apprenticelib::inputInviteCode($this->user_id, $req['code_or_phone']);

        return out(null, $res['code'], $res['msg']);
    }
    

    /*
     * 点击发放金币
     */
    public function invitationGetGold()
    {
        $goldProductRecord = GoldProductRecord::where(['user_id' => $this->user_id, 'status' => 1, 'type_key' => 'write_invite_code'])->field('id,gold_tribute,title')->find();
        if (!empty($goldProductRecord)){
            if(gold::giveOutGoldById($this->user_id, 1, $goldProductRecord['id'], $goldProductRecord['gold_tribute'])){
                return out(['gold_tribute' => $goldProductRecord['gold_tribute']]);
            }
        }

        return out(null, '10002', '请求异常，发放金币失败');
    }

    /*
     * 收徒大吉活动数据
     */
    public function apprenticeActivityData()
    {
        
        $userData = UserData::where('user_id', $this->user_id)->field('apprentice_total,effective_apprentice_num')->find();
        $rewardMoney = config('apprentice_activity');

        //收徒大吉活动计算预计收益
        $data = Apprenticelib::getApprenticeActivityGold($userData['apprentice_total']);
        $userData['expect_money'] = $data['totalMoney'];
        //用户现在进行到的阶段
        $data = Apprenticelib::getApprenticeActivityGold($userData['effective_apprentice_num']);
        $userData['now_stage'] = $data['now_stage'];
        $userData['now_stage_money'] = $data['now_stage_money'];
        $userData['stage_finish_total'] = $data['stage_finish_total'];
        $userData['stage_already_finish_num'] = $data['stage_already_finish_num'];

        return out(['userData' => $userData, 'rewardMoney' => $rewardMoney]);
    }

    /*
     * 徒弟徒孙列表
     */
    public function apprenticeList(Request $request)
    {
        $req = $request->param();
        $this->validate($req, [
            'type' => 'in:1,2',
            'page' => 'integer|>:0',
            'pageSize' => 'integer|>:0',
        ]);

        $page = !empty($req['page']) ? $req['page'] : 1;
        $pageSize = !empty($req['pageSize']) ? $req['pageSize'] : 15;

        $builder = UserApprentice::with('user')->field('apprentice_user_id,master_user_id,type,gold_tribute_total,is_effective,create_time')->where('master_user_id', $this->user_id);
        if (!empty($req['type'])){
            $builder->where('type', $req['type']);
        }

        $userApprentice = $builder->page($page, $pageSize)->select();
        $userApprentice = to_array($userApprentice);

        $builder = UserApprentice::where('master_user_id', $this->user_id);
        if (!empty($req['type'])){
            $builder->where('type', $req['type']);
        }
        $apprentice_total = $builder->count();

        $builder = UserApprentice::where('master_user_id', $this->user_id);
        if (!empty($req['type'])){
            $builder->where('type', $req['type']);
        }
        $gold_tribute_total_all = $builder->sum('gold_tribute_total');

        if (!empty($userApprentice)) {
            foreach ($userApprentice as $k => $v) {
                $userApprentice[$k]['create_date'] = date('Y-m-d', $v['create_time']);
                $userApprentice[$k]['user']['create_date'] = date('Y-m-d',$v['user']['create_time']);
                $userApprentice[$k]['user']['headimg'] = !empty($v['user']['headimg']) ? $v['user']['headimg'] :config("default_user_headimg");
            }
        }
        
        return out(['gold_tribute_total_all' => $gold_tribute_total_all, 'apprentice_total' => $apprentice_total, 'apprentice_list' => $userApprentice]);
    }

    /*
     * 分享邀请数据接口
     */
    public function invitationData(Request $request)
    {
        $req = $request->param();
        $this->validate($req, [
            'type_key' => 'max:30',
            'task_key_code' => 'max:30',
        ]);

        $config = config('share_data');
        $invitation_code = $this->userModel->where('c_user_id', $this->user_id)->value('invitation_code');

        if (!empty($req['type_key']) && $req['type_key'] == 'wake_up_apprentice') {
            $nickname = $this->userModel->where('c_user_id', $this->user_id)->value('nickname');

            $config['wake_up_apprentice']['url'] = $config['wake_up_apprentice']['base_url'].'?uid='.$this->user_id.'&type='.$config['wake_up_apprentice']['type'];

            if (!empty($req['task_key_code'])){
                $config['wake_up_apprentice']['url'] = $config['wake_up_apprentice']['url'].'&task_key_code='.$req['task_key_code'];
            }

            unset($config['wake_up_apprentice']['base_url']);

            $config['wake_up_apprentice']['title'] = sprintf($config['wake_up_apprentice']['title'], $nickname);

            if (isset($config['wake_up_apprentice']['QRcode_url'])){
                $config['wake_up_apprentice']['QRcode_url'] = $config['wake_up_apprentice']['QRcode_url'].'?uid='.$this->user_id.'&type='.$config['wake_up_apprentice']['type'];

                $filename = 'user_'.$this->user_id.'_invite_type_'.$config['wake_up_apprentice']['type'];

                if (!empty($req['task_key_code'])){
                    $config['wake_up_apprentice']['QRcode_url'] = $config['wake_up_apprentice']['QRcode_url'].'&task_key_code='.$req['task_key_code'];
                    $filename = $filename.'_taskKeyCode_'.$req['task_key_code'];
                }

                //生成用户专属的推广二维码
                $config['wake_up_apprentice']['QRcode_url'] = Util::generateWatermarkQrCode($config['wake_up_apprentice']['QRcode_url'], $filename);
            }

            $config = ['wake_up_apprentice' => $config['wake_up_apprentice']];
        }
        else {
            if (!empty($req['type_key']) && !empty($config[$req['type_key']])){
                $config = [$req['type_key'] => $config[$req['type_key']]];
            }
            foreach ($config as $k => $v){
                if (isset($v['title'])){
                    $config[$k]['title'] = sprintf($v['title'], $invitation_code);
                }
                if (isset($v['base_url'])){
                    $config[$k]['url'] = $v['base_url'].'?uid='.$this->user_id.'&type='.$v['type'];

                    if (!empty($req['task_key_code'])){
                        $config[$k]['url'] = $config[$k]['url'].'&task_key_code='.$req['task_key_code'];
                    }

                    unset($config[$k]['base_url']);
                }
                if (isset($v['QRcode_url'])){
                    $config[$k]['QRcode_url'] = $v['QRcode_url'].'?uid='.$this->user_id.'&type='.$v['type'];

                    $filename = 'user_'.$this->user_id.'_invite_type_'.$v['type'];


                    if (!empty($req['task_key_code'])){
                        $config[$k]['QRcode_url'] = $config[$k]['QRcode_url'].'&task_key_code='.$req['task_key_code'];
                        $filename = $filename.'_taskKeyCode_'.$req['task_key_code'];
                    }

                    //生成用户专属的推广二维码
                    $config[$k]['QRcode_url'] = Util::generateWatermarkQrCode($config[$k]['QRcode_url'], $filename);
                }
                //分享朋友单独处理下图片
                if ($k == 'wechat_friend_circle') {
                    array_unshift($config[$k]['imgArr'], $config[$k]['QRcode_url']);
                }
            }

            $config['invitation_code'] = $invitation_code;
        }

        return out($config);
    }

    /*
     * 收徒赚钱页面的数据
     */
    public function apprenticePageData()
    {
        $userInfo = $this->userInfo;
        $userData = UserData::where('user_id', $this->user_id)->field('apprentice_total,disciple_num')->find();
        $gold_tribute_total = UserApprentice::where(['master_user_id' => $this->user_id])->sum('gold_tribute_total');
        //是否有师傅
        $father_invitation_code = $this->userModel->where(['c_user_id'=>$userInfo['c_user_id']])->value("father_invitation_code");
        if(!empty($father_invitation_code) && $userInfo['user_father_id'] == 0){
            $is_shifu = false;
        }else{
            $is_shifu = true;
        }
        $retData = [
            'invitation_code' => $userInfo['invitation_code'],
            'apprentice_total' => $userData['apprentice_total'],
            'disciple_num' => $userData['disciple_num'],
            'gold_tribute_total' => $gold_tribute_total,
            'is_shifu'=>$is_shifu
        ];
        return out($retData);
    }

    /*
     * 师傅的信息
     */
    public function masterInfo()
    {
        $user = array();
        $userInfo = $this->userInfo;
        $master_user_id = $userInfo['user_father_id'];
        if ($master_user_id) {
            $user = User::where('c_user_id', $master_user_id)->field('c_user_id,headimg,invitation_code,nickname,total_gold_flag')->find();
            if (!empty($user)){
                $user['apprentice_total'] = $user->userData->apprentice_total;


                $user['headimg'] = !empty($user['headimg']) ? $user['headimg'] : config("default_user_headimg");

                $user['min_expected_income'] = numBit((int)$user['total_gold_flag']/2000);

                $add_expected_income = Apprenticelib::getApprenticeActivityGold($user->userData->apprentice_total)['totalMoney'];
                $user['max_expected_income'] = numBit($add_expected_income + $user['min_expected_income']);

                $user->hidden(['userData']);

                $gold_tribute_total = UserApprentice::where(['apprentice_user_id' => $this->user_id, 'master_user_id' => $master_user_id])->value('gold_tribute_total');

                $user['difference_gold'] = 1000 - $gold_tribute_total%1000;
            }
        }

        if (!empty($user)) {
            $user['have_master'] = 1;
        }
        else {
            $user['have_master'] = 2;
        }

        return out($user);
    }

    /*
     * 特权师傅页面数据
     */
    public function seniorMasterData()
    {
        $seniorData = array();
        $userInfo = $this->userInfo;
        $grade_id = $userInfo['grade_id'];
        $seniorData['is_have_apprentice'] = 1;

        if (empty($userInfo['grade_id'])){
            $grade_id = 1;
            $seniorData['is_have_apprentice'] = 2;
        }

        $seniorData['invitation_code'] = $userInfo['invitation_code'];

        $effective_apprentice_num = UserData::where('user_id', $this->user_id)->value('effective_apprentice_num');

        $multiple = Grade::where('id', $grade_id)->value('multiple');
        $seniorData['multiple'] = $multiple;

        $next_grade_id = Grade::where('need_apprentice_num', '>', $effective_apprentice_num)->order('need_apprentice_num', 'asc')->value('id');
        $next_need_apprentice_num = Grade::where('id', $next_grade_id)->value('need_apprentice_num');

        $seniorData['next_need_apprentice_num'] = $next_need_apprentice_num;

        $seniorData['next_need_apprentice_difference'] = (int)$next_need_apprentice_num - (int)$effective_apprentice_num;

        return out($seniorData);
    }
}