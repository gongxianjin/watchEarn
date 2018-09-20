<?php

namespace app\app\controller;

use think\Db;
use think\Request;
use app\app\controller\BaseController;
use app\model\User;
use app\model\TempUser;
use \Crypt;
use sms\Sms;

class Login extends BaseController
{
    /**
     * 登录
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function index()
    {
        if (!Request::instance()->isPost()) {
            //返回错误信息
            return out("错误请求", "", "error");
        }
        $request = request()->param();
        $this->validate($request, [
            'mail' => 'require',//app版本号
            'pass' => 'require',//密码
        ]);

        if (empty($request['pass'])) {
            return out("", 10001, "please enter your password");
        }
        $User = new User();

        $userMsg = $User->where(['mail' => $request['mail']])->find();

        if (empty($userMsg)) {
            return out("", 20005, "mail doesn't registered,please sign up");
        }
        if ($userMsg['login_passwd'] !== $this->getMd5Pass($request['pass'])) {
            return out("", 20006, "password error");
        }

        /**
        //一天之内 相同设备登陆5个账号则封掉这五个账号
        $safetyTime = time() - 24 * 3600;
        $meidCount = $User->where(['meid' => $this->meid, 'last_login_time' => ['gt', $safetyTime]])->count();
        if ($meidCount + 1 >= config('meidMax')) {
            Db::table('hs_user')->where(['meid' => $this->meid, 'last_login_time' => ['gt', $safetyTime]])->update(['status' => 2, 'is_cross_read_level' => 1]);
            $userMsg->status = 2;
            $userMsg->is_cross_read_level = 1;
            $userMsg->save();
            return out("", 20006, "your account has been blocked!");
        }
        */

        /**
        //一天之内 相同IP 相同推荐人 登陆超过10个账号 则封掉所有账号
        if($userMsg->user_father_id > 0){
            $ip = request()->ip();
            $ipCount = $User->where(['user_ip' => $ip, 'user_father_id' => $userMsg->user_father_id, 'last_login_time' => ['gt', $safetyTime]])->count();
            if ($ipCount + 1 >= config('ipFatherMax')) {
                Db::table('hs_user')->where(['user_ip' => $ip, 'user_father_id' => $userMsg->user_father_id, 'last_login_time' => ['gt', $safetyTime]])->update(['status' => 2, 'is_cross_read_level' => 1]);
                $userMsg->status = 2;
                $userMsg->is_cross_read_level = 1;
                $userMsg->save();
                return out("", 20006, "your account has been blocked!");
            }
        }
        */

        if ($userMsg['status'] != 1) {
            return out("", 20006, "your account has been blocked!");
        }

        $auth_token = $this->makeToken($userMsg['c_user_id'] . $userMsg['id']);
        $up = [
            "user_ip" => request()->ip(),
            "meid" => $this->meid,
            "os" => $this->os,
            "appversion" => $this->version,
            "last_login_time" => time(),
            "auth_token" => $auth_token,
        ];
        if(!empty($userMsg['auth_token'])){
            $User->clearTokenCache($userMsg['auth_token']);
        }
        if ($User->where(['id' => $userMsg['id']])->update($up)) {
            $return['ticket'] = Crypt::encrypt("token=" . $auth_token . "&login_flag=true", config("crypt_auth_key"));
            $return['login_flag'] = true;
            $return['user_info'] = User::getUserInfo($auth_token);
            //其他用户信息 ，新手红包
            return out($return);
        } else {
            return out("", 20001, "操作失败");
        }

    }

    /**
     * 微信是否绑定电话号码
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function wxbind()
    {
        if (!Request::instance()->isPost()) {
            //返回错误信息
            return out("错误请求", "", "error");
        }
        $request = request()->param();
        $this->validate($request, [
            'openid' => 'require',
            'unionid' => 'require',
            /*'nickname'=>'require',
            'headimg'=>'require',*/
        ]);
        $User = new User();
        //判断是否绑定手机
        $userMsg = $User->where(['wx_openid' => $request['openid']])->find();
        if (empty($userMsg)) {
            return out("", 20006, "未绑定手机号码");
        }
        $auth_token = $this->makeToken($userMsg['c_user_id'] . $userMsg['id']);
        $up = [
            "user_ip" => request()->ip(),
            "meid" => $this->meid,
            "os" => $this->os,
            "appversion" => $this->version,
            "last_login_time" => time(),
            "auth_token" => $auth_token,
            "nickname" => $request['nickname'],
            "headimg" => $request['headimg'],
            "unionid" => $request['unionid'],
        ];

        if ($User->where(['id' => $userMsg['id']])->update($up)) {
            $return['ticket'] = Crypt::encrypt("token=" . $auth_token . "&login_flag=true", config("crypt_auth_key"));
            $return['login_flag'] = true;
            $return['user_info'] = User::getUserInfo($auth_token);
            //其他用户信息 ，新手红包
            return out($return);
        } else {
            return out("", 10001, "操作失败");
        }
    }
}
