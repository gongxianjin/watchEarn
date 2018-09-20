<?php
namespace app\app\controller;

use app\common\service\AccessService;
use app\common\service\RetailService;
use app\common\service\Upload;
use app\model\FConfig;
use app\model\NewsType;
use app\model\VideoType;
use mail\Mail;
use think\Cache;
use think\Db;
use think\Image;
use app\app\controller\BaseController;
use app\model\User;
use app\model\TempUser;
use app\model\VideoErrorRecord;
use sms\Sms;
use think\Request;

class Pub extends BaseController
{

    public static $V_TYPE = [
        1=>['model'=>'new_video','key' => 1],
        2=>['model'=>'news',  'key'=> 2],
    ];

    /**
     * 短信发送
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function sendsms(){
        $type = input("type");
        $phone = input("phone");
        $this->validate(['type'=>$type,'phone'=>$phone], [
            'type' => 'require',
            'phone' =>'require',
        ]);
        if(!in_array($type, ['reg','findpwd'])){
            return out("",10001,"不存在类型");
        }
        //验证手机
        if(!isMobile($phone)){
            return out("",10001,"请输入正确的手机号码");
        }
        if ($type == 'reg') {
            //判断用户是否注册
            $data = db("user")->where(['telphone'=>$phone])->field("id")->find();
            if(!empty($data)){
                return out("",10001,"该手机号码已注册,请登录");
            }
        }
        $code = generate_code(4);
        $re = Sms::send_msg($code,$phone,$type);
        if($re['code']==200){
            $return = [];
            return out($return);
        }else{
            return out("",20002,$re['errmsg']);
        }
    }
    /**
     * 视频播放异常
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function videoError(){
        $video_id  = input("id/s","");
        if(empty($video_id)){
            return out([],10001,"错误请求");
        }
        $data = VideoErrorRecord::where(['video_id'=>$video_id])->find();
        if($data){
            $res = VideoErrorRecord::where(['id'=>$data['id']])->update(['video_id'=>$video_id,"e_count"=>['exp',"e_count+1"],"create_time"=>time()]);
        }else{
            $res = (new VideoErrorRecord())->save(['video_id'=>$video_id,"e_count"=>1,"create_time"=>time()]);
        }
        return out([],200,"success");
    }

    /**
     * 邮件注册
     * @return \think\response\Json
     * @throws
     */
    public function mailSendCode()
    {
        $type = input("type");
        $mail = input("mail");
        $this->validate(['type'=>$type,'mail'=>$mail], [
            'type' => 'require',
            'mail' =>'require',
        ]);

        //添加邮箱限制注册     ########################################
        $isSalf = self::checkEmail($mail);
        if(!$isSalf){
            return out("",10001,"Please use the international mailbox");
        }
        //添加邮箱限制注册    ########################################

        if(!in_array($type, ['reg','findpwd'])){
            return out("",10001,"error type");
        }
        
        $code = generate_code(4);

        try
        {
            (new Mail())->email(
                $mail
                ,"To verify your identity, please use the following code: [ $code ]"
                ,"code : [ $code ]"
            );
            Db::name("mail_code")->insert([
                'mail'=>$mail
                ,'code'=>$code
                ,'time'=>time()
            ]);
        }
        catch(\Exception $e)
        {
            return out([],10001,'send failed,please retry.');
        }

        return out([],200,'Mail sent successfully');
    }

    /**
     * 分类获取
     */
    public function getRType(){

        $videoTypes = VideoType::getLists();
        $newsTypes = NewsType::getLists();

        $lists = array_merge( $videoTypes, $newsTypes) ;

        $return=['videos'=>[],'news'=>[]];

        $typeMsg = self::$V_TYPE;

        foreach ($lists as $key => $value) {

            if($value['type'] == $typeMsg[1]['key']){
                $return['videos'][]=$value;
            }

            if($value['type'] == $typeMsg[2]['key']){
                $return['news'][]=$value;
            }

        }
        return out($return);
    }


    /**
     * 验证邮箱合法性
     *
     * @param $mail
     * @return bool|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function checkEmail($mail)
    {
        //分解用户注册邮箱
        $emailEx = explode('@',$mail);
        $mailLast = '';
        foreach ($emailEx as $val){
            if(!empty($val)){
                $mailLast = strtolower($val);
            }
        }

        //获得允许注册邮箱
        $mailKey = 'safeMail';
        $safeMail = Cache::get($mailKey);
        if(empty($safeMail)){
            $fileConfig = Db::name('f_config')->where(['name' => 'safeMail'])->find();
            $filePath = ROOT_PATH . 'public'.$fileConfig['value'];
            $mailStr  = file_get_contents($filePath);
            $safeMail = explode('@',$mailStr);
            array_filter($safeMail);
            Cache::set($mailKey,$safeMail,300);
        }

        if(in_array($mailLast,$safeMail)){
            return false;
        }

        return true;
    }

    //图片上传
    public function imageUpload()
    {
        $meid = Request::instance()->header('meid');
//        $imageFile = request()->file("uploadFile");
        $service =  new Upload();
//        $res = $service->savePath($imageFile);
        $imageFile =  file_get_contents('php://input');
        trace('log: '. $imageFile,'error');
        if(empty($imageFile)){
            return out([],10002,'data is error');
        }
        $res =  $service->saveDataPath($meid,$imageFile);
        if(!$res){
            return out([],10002,'update Error');
        }

        return out($res,200,'success');
    }

    //图片上传
    public function imageFormUpload()
    {
        $meid = Request::instance()->header('meid');
        $imageFile = request()->file("uploadFile");
        $service =  new Upload();

        $res =  $service->savePath($meid,$imageFile);
        if(!$res){
            return out([],10002,'update Error');
        }

        return out($res,200,'success');
    }

    //图片上传
    public function imageBase64Upload()
    {
        $meid = Request::instance()->header('meid');
        $imageFile = request()->file("uploadFile");
        $service =  new Upload();
        $res = $service->saveBase64Path($meid,$imageFile);
        if(!$res){
            return out([],10002,'update Error');
        }

        return out($res,200,'success');
    }


    //赚取金币排行
    function withdrawlist()
    {
        $m = new User();
        $list = $m->field('nickname,headimg,total_balance,mail')->where(['status' => 1,'is_cross_read_level' => 0])->order('total_balance desc')->limit(10)->select();
        foreach ($list as $key => $val)
        {
            if(empty($val['headimg']) || !trim($val['headimg'])){
                $list[$key]['headimg'] = "https://tg.199ho.com/static/img/default_head.png";
            }
            if(strstr($val['headimg'],'http://')){
                $val['headimg'] = str_replace("http://","https://",$val['headimg']);
            }
            if(empty($val['nickname'])){
                $list[$key]['nickname'] = explode('@',$val['mail'])[0];
            }
        }

        return out($list);
    }

    function count()
    {   $accessAddress = [
        'retail' => '招募兼职',
        'billboard' => "赚钱排行榜"
        ];
        $access = input('post.access','');
        if (empty($access)){
            return out([],10002,'page null');
        }

        $title = empty($accessAddress[$access]) ? "" : $accessAddress[$access];
        $ip = Request::instance()->ip();
        $accessService = new AccessService();

        $res = $accessService->addData($this->user_id,$access,$title,$ip);
        if($res){
            return out([],200,'success');
        }
        return out([],10002,'error');
    }
}
