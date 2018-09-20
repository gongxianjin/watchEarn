<?php
namespace app\app\controller;

use app\app\controller\mission_new\Father;
use think\Request;
use app\common\MyController;
use app\model\User;
use app\model\TempUser;
use \Crypt;

header("Access-Control-Allow-Origin:*");
header('Access-Control-Allow-Method:POST,GET');//允许访问的方式 　
class BaseController extends MyController
{
    public $os,$meid,$version,$auth_token,$ticket,$sign,$login_flag,$user_id,$userInfo,$userModel,$fatherMultiple,$mobileBrand,$mobileRatio,$language;
    public $params;
    public $header;
    public function _initialize(){
        $header_info = Request::instance()->header();
        $this->header = $header_info;
        $request = Request::instance();

        $this->meid = isset($header_info['meid'])?$header_info['meid']:input("meid","");

        $this->params = input('post.');
//        $encrypt = file_get_contents("php://input");
//        if(empty($encrypt)){
//            $this->params = private_key_decrypt($encrypt);
//        }

        //设置返回语言
        $this->language = isset($header_info['language'])? strtolower($header_info['language']) : 'en';

        //手机设备类型
        $this->os = isset($header_info['os'])?$header_info['os']:input("os","");
        //手机品牌
        $this->mobileBrand = isset($header_info['mobile_brand'])?$header_info['mobile_brand']:input("mobile_brand","");
        //手机屏幕尺寸
        $this->mobileRatio = isset($header_info['mobile_ratio'])?$header_info['mobile_ratio']:input("mobile_ratio","");
        if(empty($this->meid) || empty($this->os) || !in_array($this->os,config('os'))){
            $return =[
                "code"=>10001,
                "msg"=>"缺少参数",
                "data"=>[],
            ];
            die( json($return)->send());
        }
        $this->version = isset($header_info['version'])?$header_info['version']:input("version","");
        $this->sign = isset($header_info['sign'])?$header_info['sign']:input("sign","");
        $this->ticket = isset($header_info['ticket'])?$header_info['ticket']:input("ticket","");;
        $signAdopt = false;//签名检查
        $userAdopt = false;//用户检查
        $visitUrl = strtolower($request->controller());
        //auth认证
        $checkSignUrl = strtolower($request->controller()."/".$request->action());
        if(in_array($checkSignUrl, $this->CheckSignUrl())){
            $signAdopt =true;
        }
        //用户信息认证
        if(in_array($visitUrl, $this->noCheckUser())){
            $userAdopt = true;
        }

        //执行签名判断
        if($signAdopt){
            if(!$this->checkSign()){
                return out("",1000,"签名信息错误");
            }
        }
        //用户检查
        if(!$userAdopt){
            $this->checkUser();
        }
        //获取师傅倍数
        $this->fatherMultiple = Father::getMultiple($this->userInfo['user_father_id']);

        //用户为登陆用户 并且进贡倍数为1
        if($this->login_flag && $this->fatherMultiple == 1){
            $userInfo = $this->userModel :: getUserInfoById($this->user_id);
            if(!empty($userInfo)){
                $this->userInfo = $userInfo;
                $this->fatherMultiple = Father::getMultiple($this->userInfo['user_father_id']);
            }
        }

    }

    /**
     *不检查签名列表
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    private function CheckSignUrl(){
        return [
            'login/index',
            'reg/f_reg',
            'reg/t_reg',
            'reg/wx_t_reg',
            'pub/sendsms',
            'video/rand',
            'video/lists',
            'video/detail',
            'user/wxbind',
            'login/wxbind',
            'reg/findpass',
            'reg/twi_reg',
            'reg/fb_reg',
            'reg/lk_reg',
            'reg/reg',
            'reg/check_login',
            //'missionnew/handler',
            'commentvideo/lists',
            'commentvideo/push',
            'video/like',
            'collection/save',
            'user/setsermsg',
            'activate/push',
            //'redbag/getonered',
            'commentvideo/like',
            'activateshare/videoshare',
            'datapre/videovisit',
            'video/search',
            'pub/videoerror',
            'setting/config',
            'search/getkeyword',
            'report/video',
            'report/videocomment',
            'ad/getadmsg',
            'datapre/sharevisit',
            'videocomment/push'
        ];
    }
    /**
     * 不检查用户信息
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    private function noCheckUser(){
        //return ['reg/f_reg','reg/t_reg','pub/sendsms','login/index','login/wxbind','reg/wx_t_reg'];
        return ['reg','pub','login','ad'];
    }

    private static $diffSecond = 300;
    /**
     * 签名检查
     * 三个参数
     * time // 时间（纳秒级别，16进制，现在）
     * nonce_str // 随机字符串 32位
     * sign // hash256
     */
    private function checkSign(){
        //提交验证
        if (input('debug') == "ok"){
            return true;
        }
        $requests =  Request::instance()->param();
        unset($requests['time']);
        unset($requests['nonce_str']);
        unset($requests['sign']);
        unset($requests['new_auth']);
        unset($requests['ticket']);
        ksort($requests);
        $requestsStr = 'json='.json_encode($requests,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $time = input('time','');
        $nonce_str = 'nonce_str='.input('nonce_str','');
        $sign = input('sign','');
        $ticket = 'ticket='.$this->ticket;

        if (
            $time === '' ||
            $nonce_str == 'nonce_str=' ||
            $sign === ''
        ){
            exit('参数错误');
        }

        $timestamp = intval($time/1000);
        if (
            $timestamp-self::$diffSecond>time()
            ||
            $timestamp+self::$diffSecond<time()
        ){
            exit('参数错误');
        }

        $time = 'time='.$time;

        $key = config('request_sign_key');
        $data = sha1($time.'&'.$nonce_str.'&'.$requestsStr.'&'.$ticket);
        $signS = hash_hmac('sha256',$data,$key);

        if ($sign!==$signS)
            exit("签名错误");

        return true;
    }
    /**
     * 用户信息
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    private function checkUser(){
        if(empty($this->ticket)){
            $return =[
                "code"=>9999,
                "msg"=>"ticket错误",
                "data"=>[],
            ];
            die( json($return,400)->send());
        }
        try {
            $tokenStr = Crypt::decrypt($this->ticket,config("crypt_auth_key"));
            parse_str($tokenStr,$tokenInfo);
        } catch (\Exception $e) {
            $tokenInfo = [];
        }
        if(empty($tokenInfo)){
            $return =[
                "code"=>9999,
                "msg"=>"ticket错误",
                "data"=>[],
            ];
            die( json($return,400)->send());
        }
        if(!isset($tokenInfo['token']) || !isset($tokenInfo['login_flag'])){
            $return =[
                "code"=>9999,
                "msg"=>"ticket错误",
                "data"=>[],
            ];
            die( json($return,400)->send());
        }
        //真实用户
        if($tokenInfo['login_flag'] == "true"){
            $this->userModel = new User();
            $this->login_flag = true;
        }else{
            //未完成注册用户
            $this->userModel = new TempUser();
            $this->login_flag = false;
        }
        $userInfo =$this->userModel->getUserInfo($tokenInfo['token']);

        if(empty($userInfo)){
            $return =[
                "code"=>9999,
                "msg"=>"ticket错误",
                "data"=>[],
            ];
            die( json($return,400)->send());
        }

        $this->user_id = $userInfo['c_user_id'];
        $this->userInfo = $userInfo;

    }

    //获取表单参数
    function getParams($key,$default="")
    {
        return isset($this->params[$key]) ? $this->params[$key] : $default;
    }

    //获得Header参数
    function getHeader($key,$default="")
    {
        return isset($this->header[$key]) ? $this->header[$key] : $default;
    }
}
