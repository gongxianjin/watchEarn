<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-30
 * Time: 下午5:23
 */

namespace app\app\controller;

use app\common\MyController;
use app\model\InputInviteCodeRecord;
use app\model\TempUser;
use app\model\User;
use think\Request;

class Common extends MyController
{
    /*
     * 输入邀请码接口
     */
    public function inputInvitationCode(Request $request)
    {
        $req = $request->param();
        $this->validate($req, [
            'apprentice_phone' => 'require|length:11'
        ]);

        if(User::where('telphone', $req['apprentice_phone'])->count()){
            return out(null, '10002', '您已经注册了，快去邀请徒弟赚钱吧');
        }
        if (TempUser::where('telphone', $req['apprentice_phone'])->count()){
            return out(null, '10003', '您已经输入过了,快去注册赚零花吧');
        }

        if (!empty($req['user_id'])){
            $invitation_code = User::where('c_user_id', $req['user_id'])->value('invitation_code');
            if (!empty($invitation_code)){
                $req['invite_code'] = $invitation_code;
            }
        }

        $req['ip'] = $request->ip();
        //存在则更新
        if(InputInviteCodeRecord::where('apprentice_phone', $req['apprentice_phone'])->count()){
            $inputInviteCodeRecord = new InputInviteCodeRecord();
            $inputInviteCodeRecord->allowField(true)->save($req, ['apprentice_phone' => $req['apprentice_phone']]);
        }
        else {
            $inputInviteCodeRecord = new InputInviteCodeRecord($req);
            $inputInviteCodeRecord->allowField(true)->save();
        }

        return out();
    }
}