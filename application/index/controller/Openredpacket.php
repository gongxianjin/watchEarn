<?php
namespace app\index\controller;

use app\common\MyController;
use app\model\TempUser;
use app\model\User;
use think\Request;

class Openredpacket extends MyController
{
    public function index(Request $request)
    {
        $req = $request->param();
        $to_platfrom = input("to_platfrom","");
        $user_id = empty($req['id'])?0:$req['id'];
        $invitationCode = '';
        $headimg = '/img/taologo.png';

        //有邀请的用户id
        if (!empty($user_id)){
            $invitationCode = User::where('c_user_id','eq', $user_id)->value('invitation_code');

            if (empty($invitationCode)){
                $invitationCode = TempUser::where('c_user_id','eq', $user_id)->value('invitation_code');
            }

        }

        $this->assign('invitationCode', $invitationCode);
        $this->assign("to_platfrom",$to_platfrom);
        return $this->fetch();
    }
}