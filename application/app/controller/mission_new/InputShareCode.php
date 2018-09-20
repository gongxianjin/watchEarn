<?php
namespace app\app\controller\mission_new;

use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use app\app\library\UserTackRecord;
use app\model\GoldRun;
use app\app\library\Apprentice as Apprenticelib;
use think\Hook;


class InputShareCode extends BaseController implements MissionInterface
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

        //var_dump($this->goldRun);die;
        
    }

    /**
     *
     * 输入邀请码
     * @return array|mixed
     * @throws
     */
    function handler()
    {
        if(!$this->login_flag){
            return out('', '', 'Please log in first!');
        }
        $user_id = $this->user_id;
        $userInfo = $this->userModel :: getUserInfoById($user_id);
        if($userInfo['user_father_id'] != 0){
            return out('', 10003, 'Your inviter already exists.');
        }
        $goldRun = $this->goldRun;
        $code_or_phone = input("code_or_phone","");
        $this->validate([
            'code_or_phone'=>$code_or_phone
        ], [
            'code_or_phone' => 'require',
        ], [
            'code_or_phone.require' => 'invitation n\'t be empty ',
        ]);

        $res = Apprenticelib::inputInviteCode($this->user_id, $code_or_phone);
        if($code_or_phone == 66666){
            $gold_flag = 600;
        }else{
            $gold_flag = $goldRun['gold_flag'];
        }
        $return=[];

        return out($return, $res['code'], $res['msg']);
    }

}
