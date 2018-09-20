<?php
namespace app\app\controller;

use app\app\controller\mission_new\BindTwitter;
use app\common\service\UserService;
use app\model\GoldRun;
use app\model\MailCode;
use think\Cache;
use think\Request;
use app\app\controller\BaseController;
use app\model\User;
use app\model\TempUser;
use \Crypt;
use sms\Sms;
use app\model\UserId;
use app\model\UserData;
use think\Db;
use app\app\library\Wxbind;
use app\app\library\Apprentice;

class Reg extends BaseController
{
    /**
     * 首次进入app注册
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function f_reg(){
    	if(!Request::instance()->isPost()){
    		//返回错误信息
    		return out("错误请求","","error");
    	}
    	$request = request()->param();
        $header_info = Request::instance()->header();
    	$this->validate($request, [
            //'appversion'=>'require',//app版本号
            'mobile_brand'=>'require',//设备
        ]);
        $request['meid']=$this->meid;
        $request['os']=$this->os;
        $request['version']=$this->version;

        $TempUser = new TempUser();
        //根据meid查询对应信息
        $data = $TempUser->where(['meid'=>$request['meid']])->find();
        if(!empty($data)){
            $id = $data['id']; 
            $request['c_user_id'] = $data['c_user_id'];
        }else{
            $request['c_user_id'] =  UserId::retunCUserId();
            $request['invitation_code'] = User::makeInvitationCode();//邀请码
            if($TempUser->allowField(true)->save($request)){
                $id =  $TempUser->getLastInsID();
                UserData::create(['user_id'=>$request['c_user_id']]);
            }else{
                return out("",20001,"操作失败"); 
            }   
        }
        $auth_token = $this->makeToken($request['c_user_id'].$id);
        //var_dump($auth_token);die;
        if($TempUser->where(['id'=>$id])->update(['auth_token'=>$auth_token])){
            $return['ticket'] = Crypt::encrypt("token=".$auth_token."&login_flag=false",config("crypt_auth_key"));
            $return['login_flag'] = false;
            $return['user_info'] = TempUser::getUserInfo($auth_token);
            //其他用户信息 ，新手红包
            return out($return);
        }else{
            return out("",20001,"操作失败"); 
        }

    }

    /**
     * 真实注册
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function t_reg(){
        if(!Request::instance()->isPost()){
            //返回错误信息
            return out("错误请求","","error");
        }
        $request = request()->param();
        //$header_info = Request::instance()->header();
        //p($header_info);die;
        $this->validate($request, [
            'phone'=>'require',// 手机账号
            'verify'=>"require",// 验证码
            'mobile_brand'=>'require',//设备
            'pass'=>'require',//密码
        ]);
        //验证手机
        if(!isMobile($request['phone'])){
            return out("",10001,"请输入正确的手机号码");
        }
        if(empty($request['pass'])){
             return out("",10001,"请输入您的密码");
        }
        if(strlen($request['pass'])<6){
             return out("",20003,"密码长度为6-12位");
        }
        //验证码判断
        $type = "reg";
        $re = Sms::check($request['verify'],$type,$request['phone']);
        if($re['code']!=200){
            return out("",20004,"验证码错误");
        }
        $User = new User();
        //用户判断
        $data = $User->where(['telphone'=>$request['phone']])->find();
        if(!empty($data)){
             return out("",10001,"该手机号码已注册,请登录");
        }
        $request['user_ip'] = request()->ip();
        $request['telphone'] =$request['phone'];
        $request['login_passwd'] =$this->getMd5Pass($request['pass']);
        $request['nickname'] = substr_replace($request['telphone'],'****',3,4);
        $request['meid'] = $this->meid;
        $request['os'] = $this->os;
        $request['appversion'] = $this->version;
        $request['invitation_code'] = User::makeInvitationCode();//邀请码
        $request['create_time'] = time();
        //判断是否临时用户转为真实用户
        $c_user_id = "";
        $add_user_flag=true;
        if(!empty($this->ticket)){
            try {
                $tokenStr = Crypt::decrypt($this->ticket,config("crypt_auth_key"));
                parse_str($tokenStr,$tokenInfo);
            } catch (\Exception $e) {
                $tokenInfo = [];
            }
            if(isset($tokenInfo['token']) && isset($tokenInfo['login_flag'])){
                if($tokenInfo['login_flag'] == "false"){
                    $tempUser =Db::name("temp_user")->where(['auth_token'=>$tokenInfo['token']])->find();
                    if(!empty($tempUser)){
                        //$tempUser = TempUser::getUserInfo($tokenInfo['token']);
                        $c_user_id = $tempUser['c_user_id'];
                        $request['invitation_code'] =$tempUser['invitation_code'];//邀请码
                        $request['gold_flag'] =$tempUser['gold_flag'];//邀请码
                        $request['total_gold_flag'] =$tempUser['total_gold_flag'];//邀请码
                        $request['frozen_gold_flag'] =$tempUser['frozen_gold_flag'];//邀请码
                        $request['balance'] =$tempUser['balance'];//邀请码
                        $request['total_balance'] =$tempUser['total_balance'];//邀请码
                        $request['frozen_balance'] =$tempUser['frozen_balance'];//邀请码
                        $request['oredstatus'] =$tempUser['oredstatus'];//邀请码
                        $request['redcash'] =$tempUser['redcash'];//邀请码
                        $add_user_flag = false;
                    }
                    
                }
            }
        }
        $request['c_user_id'] = empty($c_user_id)?UserId::retunCUserId():$c_user_id;
        $request['invitation_code'] = empty($request['invitation_code'])?User::makeInvitationCode():$request['invitation_code'];
        Db::startTrans();
        try {
            if(!$User->allowField(true)->save($request)){
                 throw new \Exception('注册失败');
            }
            if($add_user_flag){
                if(!UserData::create(['user_id'=>$request['c_user_id']])){
                    throw new \Exception('注册失败');
                }
            }
            if(!$add_user_flag){
                if(!Db::name("temp_user")->where(['id'=>$tempUser['id']])->delete()){
                     throw new \Exception('注册失败');
                } 
            }
            $id =  $User->id;
            $auth_token = $this->makeToken($request['c_user_id'] .$id);

            if($User->where(['id'=>$id])->update(['auth_token'=>$auth_token])){
                $return['ticket'] = Crypt::encrypt("token=".$auth_token."&login_flag=true",config("crypt_auth_key"));
                $return['login_flag'] = true;
                $return['user_info'] = User::getUserInfo($auth_token);
                //其他用户信息 ，新手红包
               
            }else{
               throw new \Exception('注册失败'); 
            }
            Db::commit(); 
             //触发输入邀请码任务
            $f_invit_code = isset($request['f_invit_code'])?$request['f_invit_code']:"";
            $up_code_record_status = false;
            if(empty($f_invit_code)){
                //查询用户是否存在填写记录
                $f_invit_code_msg = Db::name('input_invite_code_record')
                                ->where([
                                    'apprentice_phone'=>$request['phone'],
                                    'user_id'=>['>',0],
                                    'status'=>1,
                                ])
                                ->field("invite_code,id")
                                ->find();
                if(!empty($f_invit_code_msg)){
                    $f_invit_code = $f_invit_code_msg['invite_code'];
                    $up_code_record_status = true;
                }
            }
            if(!empty($f_invit_code)){
               $input_code_res =  Apprentice::inputInviteCode($request['c_user_id'],$f_invit_code);
               if($input_code_res['code'] == 200 && $up_code_record_status){
                   Db::name("input_invite_code_record")->where(['id'=>$f_invit_code_msg['id']])->update([
                        'status'=>2,
                        'update_time'=>time(),
                   ]);
               }
            }
            return out($return);
        } catch (\Exception $e) {
            Db::rollback();
            return out("",10001,$e->getMessage());
        }
    }


    public function m_reg()
    {
        if(!Request::instance()->isPost()){
            //返回错误信息
            return out("错误请求","","error");
        }
        $request = request()->param();
        //$header_info = Request::instance()->header();
        //p($header_info);die;
        $this->validate($request, [
            'mail'=>'require',// 邮箱
            'verify'=>"require",// 验证码
            'mobile_brand'=>'require',//设备
            'pass'=>'require',//密码
        ]);

        //添加邮箱限制注册     ########################################
        $isSalf = Pub::checkEmail($request['mail']);
        if(!$isSalf){
            return out("",10001,"Please use the international mailbox");
        }
        //添加邮箱限制注册    ########################################

        if(empty($request['pass'])){
            return out("",10001,"请输入您的密码");
        }
        if(strlen($request['pass'])<6){
            return out("",20003,"密码长度为6-12位");
        }

        //验证码判断
        $re = MailCode
            ::where('mail','eq',$request['mail'])
            ->where('code','eq',$request['verify'])
            ->where('time','gt',time()-60*5)
            ->find();

        if($re==null){
            return out("",20004,"code error");
        }
        $User = new User();

        //每个设备ID只能注册三个账号
        $regNum = $User->where(['meid' => $this->meid])->count();
        if($regNum >= config('registerMax')){
            return out('',20002,'Nos of accounts on a device are limited!');
        }

        //用户判断
        $data = $User->where(['mail'=>$request['mail']])->find();
        if(!empty($data)){
            return out("",10001,"mail already registered, please sign in");
        }
        $request['user_ip'] = request()->ip();
        $request['mail'] =$request['mail'];
        $request['login_passwd'] =$this->getMd5Pass($request['pass']);

        $request['nickname'] = substr($request['mail'],0,strpos($request['mail'],'@'));

        $request['meid'] = $this->meid;
        $request['os'] = $this->os;
        $request['appversion'] = $this->version;
        $request['invitation_code'] = User::makeInvitationCode();//邀请码
        $request['create_time'] = time();
        //判断是否临时用户转为真实用户
        $c_user_id = "";
        $add_user_flag=true;
        if(!empty($this->ticket)){
            try {
                $tokenStr = Crypt::decrypt($this->ticket,config("crypt_auth_key"));
                parse_str($tokenStr,$tokenInfo);
            } catch (\Exception $e) {
                $tokenInfo = [];
            }
            if(isset($tokenInfo['token']) && isset($tokenInfo['login_flag'])){
                if($tokenInfo['login_flag'] == "false"){
                    $tempUser =Db::name("temp_user")->where(['auth_token'=>$tokenInfo['token']])->find();
                    if(!empty($tempUser)){
                        //$tempUser = TempUser::getUserInfo($tokenInfo['token']);
                        $c_user_id = $tempUser['c_user_id'];
                        $request['invitation_code'] =$tempUser['invitation_code'];//邀请码
                        $request['gold_flag'] =$tempUser['gold_flag'];//邀请码
                        $request['total_gold_flag'] =$tempUser['total_gold_flag'];//邀请码
                        $request['frozen_gold_flag'] =$tempUser['frozen_gold_flag'];//邀请码
                        $request['balance'] =$tempUser['balance'];//邀请码
                        $request['total_balance'] =$tempUser['total_balance'];//邀请码
                        $request['frozen_balance'] =$tempUser['frozen_balance'];//邀请码
                        $request['oredstatus'] =$tempUser['oredstatus'];//邀请码
                        $request['redcash'] =$tempUser['redcash'];//邀请码
                        $add_user_flag = false;
                    }

                }
            }
        }
        $request['c_user_id'] = empty($c_user_id)?UserId::retunCUserId():$c_user_id;
        $request['invitation_code'] = empty($request['invitation_code'])?User::makeInvitationCode():$request['invitation_code'];
        Db::startTrans();
        try {
            if(!$User->allowField(true)->save($request)){
                throw new \Exception('注册失败');
            }
            if($add_user_flag){
                if(!UserData::create(['user_id'=>$request['c_user_id']])){
                    throw new \Exception('注册失败');
                }
            }
            if(!$add_user_flag){
                if(!Db::name("temp_user")->where(['id'=>$tempUser['id']])->delete()){
                    throw new \Exception('注册失败');
                }
            }
            $id =  $User->id;
            $auth_token = $this->makeToken($request['c_user_id'] .$id);
            //注册自动登陆  更新最近登陆时间
            $loginTime = time();

            //用户注册成功 互通用户信息
            $unique = uuid();
            $userService = new UserService();
            if(!$userService->createUser($unique,$request['mail'],$request['nickname'])){
                $unique = "";
            }

            if($User->where(['id'=>$id])->update(['auth_token'=>$auth_token,'last_login_time' => $loginTime,'unique' => $unique])){
                $return['ticket'] = Crypt::encrypt("token=".$auth_token."&login_flag=true",config("crypt_auth_key"));
                $return['login_flag'] = true;
                $return['user_info'] = User::getUserInfo($auth_token);
                //其他用户信息 ，新手红包

            }else{
                throw new \Exception('注册失败');
            }
            Db::commit();
            //触发输入邀请码任务
            $f_invit_code = isset($request['f_invit_code'])?$request['f_invit_code']:"";

            if(!empty($f_invit_code)){
                Apprentice::inputInviteCode($request['c_user_id'],$f_invit_code);
            }
            return out($return);
        } catch (\Exception $e) {
            Db::rollback();
            return out("",10001,$e->getMessage());
        }
    }

    /**
     * 微信注册
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function wx_t_reg(){
        if(!Request::instance()->isPost()){
            //返回错误信息
            return out("",10001,"错误请求");
        }
        $request = request()->param();
        $this->validate($request, [
            'phone'=>'require',//app版本号
            'verify'=>"require",//yanzhengam 
            'mobile_brand'=>'require',//设备
            'pass'=>'require',//密码
            'nickname'=>'require',//名称
            'headimg'=>"require",//头像 
            'openid'=>'require',//设备openid
            'sex'=>'require',
            'unionid'=>'require',
        ]);

        if(empty($request['pass'])){
            return out("",10001,"请输入您的密码");
        }
        if(strlen($request['pass'])<6){
            return out("",20003,"密码长度为6-12位");
        }
        if(!in_array($request['sex'], [1,2])){
            return out("",10001,"sex错误");
        }
        //验证码判断
        $type = "reg";
        $re = Sms::check($request['verify'],$type,$request['phone']);
        if($re['code']!=200){
            return out("",20004,"验证码错误");
        }
        $User = new User();
        //用户判断
        $data = $User->where(['telphone'=>$request['phone']])->find();
        if(!empty($data)){
            return out("",10001,"该手机号码已注册,请登录");
        }
        $request['user_ip'] = request()->ip();
        $request['telphone'] =$request['phone'];
        $request['login_passwd'] =$this->getMd5Pass($request['pass']);
        $request['nickname'] = $request['nickname'];
        $request['wx_openid'] = $request['openid'];
        $request['unionid'] = $request['unionid'];
        $request['meid'] = $this->meid;
        $request['os'] = $this->os;
        $request['appversion'] = $this->version;
        $request['create_time'] = time();
        //判断是否临时用户转为真实用户
        $c_user_id = "";
        $add_user_flag=true;
        if(!empty($this->ticket)){
            try {
                $tokenStr = Crypt::decrypt($this->ticket,config("crypt_auth_key"));
                parse_str($tokenStr,$tokenInfo);
            } catch (\Exception $e) {
                $tokenInfo = [];
            }
            if(isset($tokenInfo['token']) && isset($tokenInfo['login_flag'])){
                if($tokenInfo['login_flag'] == "false"){
                    $tempUser =Db::name("temp_user")->where(['auth_token'=>$tokenInfo['token']])->find();
                    if(!empty($tempUser)){
                        //$tempUser = TempUser::getUserInfo($tokenInfo['token']);
                        $c_user_id = $tempUser['c_user_id'];
                        $request['invitation_code'] =$tempUser['invitation_code'];//邀请码
                        $request['gold_flag'] =$tempUser['gold_flag'];//邀请码
                        $request['total_gold_flag'] =$tempUser['total_gold_flag'];//邀请码
                        $request['frozen_gold_flag'] =$tempUser['frozen_gold_flag'];//邀请码
                        $request['balance'] =$tempUser['balance'];//邀请码
                        $request['total_balance'] =$tempUser['total_balance'];//邀请码
                        $request['frozen_balance'] =$tempUser['frozen_balance'];//邀请码
                        $request['oredstatus'] =$tempUser['oredstatus'];//邀请码
                        $request['redcash'] =$tempUser['redcash'];//邀请码
                        $add_user_flag = false;
                    }

                }
            }
        }
        $request['c_user_id'] = empty($c_user_id)?UserId::retunCUserId():$c_user_id;
        $request['invitation_code'] = empty($request['invitation_code'])?User::makeInvitationCode():$request['invitation_code'];
        Db::startTrans();
        try {
            if(!$User->allowField(true)->save($request)){
                throw new \Exception('注册失败');
            }
            if($add_user_flag){
                if(!UserData::create(['user_id'=>$request['c_user_id']])){
                    throw new \Exception('注册失败');
                }
            }
            if(!$add_user_flag){
                if(!Db::name("temp_user")->where(['id'=>$tempUser['id']])->delete()){
                    throw new \Exception('注册失败');
                }
            }
            $id =  $User->id;
            $auth_token = $this->makeToken($request['c_user_id'] .$id);
            //触发完成微信绑定任务
            $bind_res = Wxbind::wxbind($request['c_user_id']);
            if($bind_res['code'] != 200){
                throw new \Exception($bind_res['msg']);
            }
            if($User->where(['id'=>$id])->update(['auth_token'=>$auth_token])){
                $return['ticket'] = Crypt::encrypt("token=".$auth_token."&login_flag=true",config("crypt_auth_key"));
                $return['login_flag'] = true;
                $return['user_info'] = User::getUserInfo($auth_token);
                //其他用户信息 ，新手红包
            }else{
                throw new \Exception('注册失败');
            }
            Db::commit();
            //触发输入邀请码任务
            $f_invit_code = isset($request['f_invit_code'])?$request['f_invit_code']:"";
            $up_code_record_status = false;
            if(empty($f_invit_code)){
                //查询用户是否存在填写记录
                $f_invit_code_msg = Db::name('input_invite_code_record')
                    ->where([
                        'apprentice_phone'=>$request['phone'],
                        'user_id'=>['>',0],
                        'status'=>1,
                    ])
                    ->field("invite_code,id")
                    ->find();
                if(!empty($f_invit_code_msg)){
                    $f_invit_code = $f_invit_code_msg['invite_code'];
                    $up_code_record_status = true;
                }
            }
            if(!empty($f_invit_code)){
                $input_code_res =  Apprentice::inputInviteCode($request['c_user_id'],$f_invit_code);
                if($input_code_res['code'] == 200 && $up_code_record_status){
                    Db::name("input_invite_code_record")->where(['id'=>$f_invit_code_msg['id']])->update([
                        'status'=>2,
                        'update_time'=>time(),
                    ]);
                }
            }
            return out($return);
        } catch (Exception $e) {
            Db::rollback();
            return out("",10001,$e->getMessage());
        }
    }


    /**
     * twitter 注册
     *
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function twi_reg(){
        if(!Request::instance()->isPost()){
            //返回错误信息
            return out("",10001,"错误请求");
        }
        $request = request()->param();
        $this->validate($request, [
            'mail'=>'require',//app版本号
            'verify'=>"require",//yanzhengam
            'mobile_brand'=>'require',//设备
            'pass'=>'require',//密码
            'nickname'=>'require',//名称
            'headimg'=>"require",//头像
            'twitter_id'=>'require',//设备openid
            'sex'=>'require',
        ]);

        return $this->reg($request);
    }

    /**
     * fb 注册
     *
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fb_reg(){
        if(!Request::instance()->isPost()){
            //返回错误信息
            return out("",10001,"错误请求");
        }
        $request = request()->param();
        $this->validate($request, [
            'mail'=>'require',//app版本号
            'verify'=>"require",//yanzhengam
            'mobile_brand'=>'require',//设备
            'pass'=>'require',//密码
            'nickname'=>'require',//名称
            'headimg'=>"require",//头像
            'fb_id'=>'require',//fb openid
            'fb_access_token'=>'require',//fb access token
            'sex'=>'require',
        ]);
        return $this->reg($request);
    }


    public function lk_reg()
    {
        if(!Request::instance()->isPost()){
            //返回错误信息
            return out("",10001,"错误请求");
        }
        $request = request()->param();
        $this->validate($request, [
            'mail'=>'require',//app版本号
            'verify'=>"require",//yanzhengam
            'mobile_brand'=>'require',//设备
            'pass'=>'require',//密码
            'nickname'=>'require',//名称
            'headimg'=>"require",//头像
            'lk_id'=>'require',//lk openid
            'sex'=>'require',
        ]);
        return $this->reg($request);
    }


    /**
     * @param $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function reg($request)
    {
        if(!in_array($request['sex'], [1,2])){
            return out("",10001,"sex错误");
        }

        //验证码判断
        $re = MailCode
            ::where('mail','eq',$request['mail'])
            ->where('code','eq',$request['verify'])
            ->where('time','gt',time()-60*5)
            ->find();

        if($re==null){
            return out("",20004,"code error");
        }

        $User = new User();
        //用户判断
        $finder = $User
            ->where(['mail'=>$request['mail']]);

        //各平台ID
        if (isset($request['twitter_id']))
            $finder->whereOr('twitter_id','eq',$request['twitter_id']);
        else if (isset($request['fb_id']))
            $finder->whereOr('fb_id','eq',$request['fb_id']);
        else if (isset($request['lk_id']))
            $finder->whereOr('lk_id','eq',$request['lk_id']);

        $data = $finder->find();
        if(!empty($data)){

            if ( isset( $request['twitter_id'] ) && empty( $data['twitter_id'] ) )
            {
                // 额外绑定twiiter_id
                $User::update([
                    'twitter_id'=>$request['twitter_id']
                ],[
                    'c_user_id'=>$data['c_user_id']
                ]);
            }
            else if ( isset( $request['fb_id'] ) && empty( $data['fb_id'] ) )
            {
                // 额外绑定fb_id
                $User::update([
                    'fb_id'=>$request['fb_id']
                    ,'fb_access_token'=>$request['fb_access_token']
                ],[
                    'c_user_id'=>$data['c_user_id']
                ]);
            }
            else if ( isset( $request['lk_id'] ) && empty( $data['lk_id'] ) )
            {
                // 额外绑定lk_id
                $User::update([
                    'lk_id'=>$request['lk_id']
                ],[
                    'c_user_id'=>$data['c_user_id']
                ]);
            }

            if ( empty($data['headimg'])  )
            {
                $User::update([
                    'nickname'=>$request['nickname']
                    ,'headimg'=>$request['headimg']
                    ,'sex'=>$request['sex'],
                ],[
                    'c_user_id'=>$data['c_user_id']
                ]);
            }

            $auth_token = $this->makeToken($data['c_user_id'] .$data['id']);

            $unique = "";
            $upData = ['auth_token'=>$auth_token,'last_login_time' => time()];
            //绑定成功 响应同步用户信息
            if(!empty($data['unique'])){
                $unique = $data['unique'];
            }else{
                $unique = uuid();
                $upData['unique'] = $unique;
            }
            $userService = new UserService();
            $userService->createUser($unique,$request['mail'],$request['nickname'],$request['headimg']);

            if($User->where(['c_user_id'=>$data['c_user_id']])->update($upData)){
                $return['ticket'] = Crypt::encrypt("token=".$auth_token."&login_flag=true",config("crypt_auth_key"));
                $return['login_flag'] = true;
                $return['user_info'] = User::getUserInfo($auth_token);
            }else{
                throw new \Exception('注册失败');
            }

            return out($return);
        }
        $request['user_ip'] = request()->ip();
        $request['login_passwd'] =$this->getMd5Pass($request['pass']);
        $request['meid'] = $this->meid;
        $request['os'] = $this->os;
        $request['appversion'] = $this->version;
        $request['create_time'] = time();
        //判断是否临时用户转为真实用户
        $c_user_id = "";
        $add_user_flag=true;
        if(!empty($this->ticket)){
            try {
                $tokenStr = Crypt::decrypt($this->ticket,config("crypt_auth_key"));
                parse_str($tokenStr,$tokenInfo);
            } catch (\Exception $e) {
                $tokenInfo = [];
            }
            if(isset($tokenInfo['token']) && isset($tokenInfo['login_flag'])){
                if($tokenInfo['login_flag'] == "false"){
                    $tempUser =Db::name("temp_user")->where(['auth_token'=>$tokenInfo['token']])->find();
                    if(!empty($tempUser)){
                        $c_user_id = $tempUser['c_user_id'];
                        $request['invitation_code'] =$tempUser['invitation_code'];//邀请码
                        $request['gold_flag'] =$tempUser['gold_flag'];//邀请码
                        $request['total_gold_flag'] =$tempUser['total_gold_flag'];//邀请码
                        $request['frozen_gold_flag'] =$tempUser['frozen_gold_flag'];//邀请码
                        $request['balance'] =$tempUser['balance'];//邀请码
                        $request['total_balance'] =$tempUser['total_balance'];//邀请码
                        $request['frozen_balance'] =$tempUser['frozen_balance'];//邀请码
                        $request['oredstatus'] =$tempUser['oredstatus'];//邀请码
                        $request['redcash'] =$tempUser['redcash'];//邀请码
                        $add_user_flag = false;
                    }
                }
            }
        }
        $request['c_user_id'] = empty($c_user_id)?UserId::retunCUserId():$c_user_id;
        $request['invitation_code'] = empty($request['invitation_code'])?User::makeInvitationCode():$request['invitation_code'];
        Db::startTrans();
        try {
            if(!$User->allowField(true)->save($request)){
                throw new \Exception('注册失败');
            }
            if($add_user_flag){
                if(!UserData::create(['user_id'=>$request['c_user_id']])){
                    throw new \Exception('注册失败');
                }
            }
            if(!$add_user_flag){
                if(!Db::name("temp_user")->where(['id'=>$tempUser['id']])->delete()){
                    throw new \Exception('注册失败');
                }
            }
            $id =  $User->id;
            $auth_token = $this->makeToken($request['c_user_id'] .$id);

            //用户注册成功 互通用户信息
            $unique = uuid();
            $userService = new UserService();
            if(!$userService->createUser($unique,$request['mail'],$request['nickname'],$request['headimg'])){
                $unique = "";
            }
            if($User->where(['id'=>$id])->update(['auth_token'=>$auth_token,'unique' => $unique])){
                $return['ticket'] = Crypt::encrypt("token=".$auth_token."&login_flag=true",config("crypt_auth_key"));
                $return['login_flag'] = true;
                $return['user_info'] = User::getUserInfo($auth_token);
                //其他用户信息 ，新手红包
            }else{
                throw new \Exception('注册失败');
            }
            Db::commit();
            //触发输入邀请码任务
            $f_invit_code = isset($request['f_invit_code'])?$request['f_invit_code']:"";
            if(!empty($f_invit_code)){
                Apprentice::inputInviteCode($request['c_user_id'],$f_invit_code);
            }
            return out($return);
        } catch (\Exception $e) {
            Db::rollback();
            return out("",10001,$e->getMessage());
        }
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function check_login()
    {
        $User = new User();
        $request = Request::instance()->param();
        //用户判断
        $finder = null;
        //各平台ID
        if (isset($request['twitter_id']))
            $finder = $User->where('twitter_id','eq',$request['twitter_id']);
        else if (isset($request['fb_id']))
            $finder = $User->where('fb_id','eq',$request['fb_id']);
        else if (isset($request['lk_id']))
            $finder = $User->where('lk_id','eq',$request['lk_id']);

        $data =  $finder->find();
        if(!empty($data)){

            $auth_token = $this->makeToken($data['c_user_id'].$data['id']);
            $up=[
                "user_ip"=>request()->ip(),
                "meid"=>$this->meid,
                "os"=>$this->os,
                "appversion"=>$this->version,
                "last_login_time"=>time(),
                "auth_token"=>$auth_token,
            ];
            if($User->where(['id'=>$data['id']])->update($up)){
                $return['ticket'] = Crypt::encrypt("token=".$auth_token."&login_flag=true",config("crypt_auth_key"));
                $return['login_flag'] = true;
                $return['user_info'] = User::getUserInfo($auth_token);
                //其他用户信息 ，新手红包
                return out($return,'302');
            }else{
                return out("",20001,"操作失败");
            }
        }

        return out();
    }

    /**
     * 忘记密码
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function findpass(Request $request)
    {
        $req = $request->param();
        $this->validate($req, [
            'phone' => 'require|integer|length:11',
            'verify' => 'require|integer|length:4',
            'pass' => 'require|length:6,12',
        ], [
            'phone.require' => '电话号码不能为空',
            'phone.integer' => '请输入正确的手机号码',
            'phone.length' => '请输入正确的手机号码',
            'verify.require' => '验证码不能为空',
            'pass.require' => '密码不能为空',
            'pass.length' => '密码长度为6-12位',
        ]);

        //验证码判断
        $type = "findpwd";
        $re = Sms::check($req['verify'], $type, $req['phone']);
        if($re['code'] != 200){
            return out('', 20004, '验证码错误');
        }
        //用户判断
        $user = User::where(['telphone' => $req['phone']])->field('login_passwd')->find();
        if(empty($user)){
            return out("", 10001, "该手机号码不存在，请注册");
        }
        //保存新密码
        $user->login_passwd = $this->getMd5Pass($req['pass']);
        $user->save();

        return out();
    }


    /**
     * 忘记密码
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function m_findpass(Request $request)
    {
        $req = $request->param();
        $this->validate($req, [
            'mail' => 'require',
            'verify' => 'require|length:4',
            'pass' => 'require|length:6,12',
        ], [
            'verify.require' => 'verify couldn\'t be empty',
            'verify.length' => 'verify length must 4',
            'pass.require' => 'pass couldn\'t be empty',
            'pass.length' => 'password length must between 6 and 12',
        ]);

        //验证码判断
        $re = MailCode
            ::where('mail','eq',$req['mail'])
            ->where('code','eq',$req['verify'])
            ->where('time','gt',time()-60*5)
            ->find();

        if($re==null){
            return out("",20004,"code error");
        }

        //用户判断
        $user = User::where(['mail' => $req['mail']])->field('login_passwd')->find();
        if(empty($user)){
            return out("", 10001, "mail doesn't exists, please sign up");
        }
        //保存新密码
        $user->login_passwd = $this->getMd5Pass($req['pass']);
        $user->save();

        return out();
    }
}
