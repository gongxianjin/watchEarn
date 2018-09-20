<?php

/**

 * 短信发送类

 * @param  模型，引用传递

 * @param  查询条件

 * @param int  每页查询条数

 * @return 返回

 */

namespace sms;

use think\Db;



class Sms {



     public static function initconf(){

        $config = [

            'checktel'=>['life_time'=>1800,],//s

            'reg'=>['life_time'=>1800],//s

            'login'=>['life_time'=>1800],

            'findpwd'=>['life_time'=>1800],

            'paypwd'=>['life_time'=>1800],

        ];

        return $config;

    }



    /**

     * 获取短信

     */

    public static function send_msg($code,$phone,$type){


        $ip = request()->ip(1);

        //$token = $phone.str_pad($ip,10,'0').parsekey($type);
        $token = $phone.parsekey($type);

        $time = time();

        $data = self::find($token);

        $count = self::send_count($phone);

        if($count>3){
            return ['code'=>0,'errmsg'=>"您的操作太频繁,请稍后再试"];
        }
        if($data){
            if(($data['add_time']+60)>$time){
                return ['code'=>0,'errmsg'=>"您的操作太频繁,请稍后再试"];
            }
            /*else{

                $data['add_time'] = $time;

                $data['auth_code'] = $auth_code = $code;

                self::update($token,$data);

            }*/
        }
        $data =[];
        $data['token'] = $token;
        $data['mobile'] = $phone;
        $data['add_time'] = $time;
        $data['auth_code'] = $auth_code = $code;
        self::add($data);
         //$config = self::initconf();
        // var_dump($config);die;
        //发送短信
/*        $statusStr = array(

                "0" => "短信发送成功",

                "-1" => "参数不全",

                "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",

                "30" => "密码错误",

                "40" => "账号不存在",

                "41" => "余额不足",

                "42" => "帐户已过期",

                "43" => "IP地址限制",

                "50" => "内容含有敏感词"

        );  */

        $smsapi = "http://www.smsbao.com/"; //短信网关

        $user = "2924279490"; //短信平台帐号

        $pass = md5("292427940"); //短信平台密码

        $content="【淘视界】"."您本次的验证码为".$code.",千万不要告诉其他人哦";//要发送的短信内容

        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);

        $result =file_get_contents($sendurl);

        if($result == 0){

            return ['code'=>200,'errmsg'=>"短信发送成功"];

        }else{

            return ['code'=>0,'errmsg'=>"短信发送失败"];

        }

    }

    //验证码验证

    public static function check($code,$type,$mobile){

        $config = self::initconf();

        if(!isset($config[$type])){

            return ['code'=>0,'不可接受参数'];

        }

        $ip = request()->ip(1);

        //$token = $mobile.str_pad($ip,10,'0').parsekey($type);
        $token = $mobile.parsekey($type);



        $time = time();

        $data = self::find($token);



        if($data){

            if(($data['add_time']+$config[$type]['life_time'])<$time){

                self::rm($token);

                return ['code'=>0,'errmsg'=>lang('验证失败')];

            }

            if($data['auth_code']==$code){

                self::rm($token);

                return ['code'=>200,'errmsg'=>lang('验证成功')];

            }

        }

        return ['code'=>0,'errmsg'=>lang('验证码错误')];

    }





    //增删改查

    public static function add($data){

        Db::name("sms_record")->insert($data);

    }



    public static function rm($token){

        Db::name("sms_record")->where("token='".$token."'")->delete();

    }



    public static function update($token,$data){

        Db::name("sms_record")->where("token='".$token."'")->update($data);

    }



    public static function find($token){

        $data = Db::name("sms_record")->where("token='$token'")->order("add_time DESC,id DESC")->find();

        return $data;

    }


    public static function send_count($mobile){

        $map['mobile'] = $mobile;

        $count = Db::name("sms_record")->where($map)->whereTime('add_time','today')->count();



        return $count;

    }





}