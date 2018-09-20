<?php
namespace app\app\controller;

use app\app\controller\BaseController;
use app\common\logic\UserVideoLogic;
use app\common\service\RetailService;
use app\common\service\Upload;
use app\common\service\UserService;
use app\common\service\UserVideoLike;
use app\common\service\UserVideoService;
use app\model\DummyFollow;
use app\model\DummyUser;
use app\model\NewVideo;
use app\model\ShareVisit;
use app\model\UserVideo;
use think\Request;
use think\Session;
use think\Db;
use app\app\library\Gold;
use app\model\GoldProductRecord;
use app\model\Video;
class User extends BaseController
{
    /**
     *用户信息
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function index(){

        $userInfo = $this->userInfo;
        $loginStr = "";
        if($this->login_flag){
            $loginStr = ",a.paypal_mail";
        }
        $otherMsg = $this->userModel
                    ->alias("a")
                    ->join("__USER_DATA__ ud","ud.user_id = a.c_user_id")
                    ->where(['a.c_user_id'=>$this->user_id])
                    ->field("a.id,a.nickname,a.user_father_id,a.father_invitation_code,a.oredstatus,a.redcash,a.wx_openid,a.balance,a.gold_flag,a.total_balance,ud.apprentice_total".$loginStr)
                    ->find();
     
        if(empty($userInfo['headimg'])){
            $userInfo['headimg'] = config("default_user_headimg");
        }
        $share_code_status = false;//未完成
        //是否注册邀请码
        if($otherMsg['user_father_id']>0 || !empty($otherMsg['father_invitation_code'])){
            $share_code_status = true;
        }
        $redcash_status = false;//未完成
        //是否完成新人一元提现
        if($otherMsg['redcash'] == 1){
            $redcash_status  = true;
        }

        // 是否应该隐藏
        $sql = '
        select (
(select 1 count from hs_gold_product_record where type_key = \'usual_read\' and  user_id = ? limit 1 )
+
(select 1 count from hs_gold_product_record where type_key = \'share_friend_prentice\' and user_id = ? limit 1 )) \'count\';
        
        ';

        $return['userMsg'] = [
             "nickname"=>$otherMsg['nickname'],
             "headimg"=>$userInfo['headimg'],
             "invitation_code"=>$userInfo['invitation_code'],
             "gold_flag"=>$otherMsg['gold_flag'],
             "balance"=>$otherMsg['balance'],
             "total_balance"=>$otherMsg['total_balance'],
             "share_code_status"=>$share_code_status,
             "ored_status"=> ($otherMsg['oredstatus'] ==1)?true:false,
             "redcash_status"=>$redcash_status,
             "apprentice_status"=>($otherMsg['apprentice_total'] > 0)?true:false,
             "is_bind_wx"=>!empty($otherMsg['wx_openid'])?true:false,
            "is_has_paypal"=>!empty($otherMsg['paypal_mail'])?true:false,
            "paypal_mail"=>empty($otherMsg['paypal_mail'])?'':$otherMsg['paypal_mail'],
             "min_draw"=>5,
             "max_draw"=> intval($userInfo['balance']/5)*5+5,
            "is_hidden_first_mission"=> Db::query($sql,[$userInfo['c_user_id'],$userInfo['c_user_id']])[0]['count']==2
        ];
        $return['login_flag'] = $this->login_flag;
       /* $xin_user = [
            "user_id"=>$this->user_id,
            "login_flag"=>$this->login_flag,
        ];*/
        //Session::set('xin_user',$xin_user);
        return  out($return);
    }

    //获取其他用户信息
    function workInfo()
    {
        $user_id = $this->user_id;
        $params = $this->params;
        $du_type = isset($params['du_type']) ? $params['du_type'] : 2;
        $other_id = isset($params['other_id']) ? $params['other_id'] : '';

        //不传则显示自己关注信息接口
        if(empty($other_id)){
           $other_id = $user_id;
            $du_type = 1;
        }

        if($du_type == 1){
            $retInfo = [];
            $user = new \app\model\User();
            $OtherInfo = $user->where(['c_user_id' => $other_id])->find();
            if(empty($OtherInfo)){
                return out([],200,'user error');
            }

            $retInfo['user_id'] = $other_id;
            $retInfo['nickname'] = $OtherInfo->nickname;
            $retInfo['userAvatar'] = empty($OtherInfo->headimg) ? "http://tg.199ho.com//static/img/default_head.png" : $OtherInfo->headimg;
            $retInfo['birthday'] = $OtherInfo->birthday;
            $retInfo['sex'] = $OtherInfo->sex;
            $retInfo['followNum'] = $OtherInfo->follow_num;
            $retInfo['fansNum'] = $OtherInfo->fans_num;
            $retInfo['likeCount'] = $OtherInfo->like_count;
            $retInfo['getLike'] = $OtherInfo->get_like;
            $retInfo['signature'] = empty($OtherInfo->signature)? "": $OtherInfo->signature;
            $retInfo['isFollow'] = false;
            $retInfo['du_type'] = 1;
            $service = new UserService();

            if($user_id != $other_id){
                $retInfo['isFollow'] = $service->getFollowStatus($user_id,$other_id);
            }
        }else{
            $user = new DummyUser();
            $follow = new DummyFollow();
            $video = new NewVideo();
            $other = $user->find($other_id);
            if(empty($other)){
                return out([],200,'user error');
            }
            $isNewRecord = $follow->where(['user_id' => $this->user_id,'follow_user_id'=> $other_id,'status' => 1])->find();
            $isFollow = false;
            if(!empty($isNewRecord) && $isNewRecord['status'] == 1) $isFollow = true;
            //虚假用户生成关注数量
            $likeUserKey = "DummyUserFollow_"  . $other_id;
            $followNum = cache($likeUserKey);
            if(empty($followNum)){
                $followNum = rand(1,20);
                cache($likeUserKey,$followNum);
            }

            //虚假用户生成喜欢视频数量
            $likeVideoKey = "DummyUserLikeVideo_"  . $other_id;
            $likeVideoNum = cache($likeVideoKey);
            if(empty($likeVideoNum)){
                $likeVideoNum = rand(6,50);
                cache($likeVideoKey,$followNum);
            }

            $retInfo['nickname'] = $other['nickname'];;
            $retInfo['userAvatar'] = $other['user_avatar'];
            $retInfo['birthday'] = '';
            $retInfo['sex'] = 1;
            $retInfo['followNum'] = $followNum;
            $retInfo['fansNum'] = $follow->where(['follow_user_id'=> $other_id,'status' => 1])->count();
            $retInfo['likeCount'] = $likeVideoNum;
            $retInfo['getLike'] = $video->where(['du_id' => $other_id])->sum('like_count');
            $retInfo['signature'] = '';
            $retInfo['isFollow'] = $isFollow;
            $retInfo['du_type'] = 2;
            $retInfo['user_id'] = $other_id;
        }

        return out($retInfo,200,"success");
    }

    /**
     * 红包消息，是否存在消息,未完成任务
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function infoHints(){
      //用户是否可领取宝箱，可签到
      $s_today = strtotime(date('Y-m-d'));
      $pre_chest =time()-14400;//宝箱4小时
      $map = "((task_id = 22 AND update_time > {$s_today}) OR (task_id = 23 AND update_time > {$pre_chest})) AND user_id =".$this->user_id;
      $tiskMsg = Db::name("user_task_record")->where($map)->count();
      //$otherRed =
      $oredstatus = $this->userInfo['oredstatus'];
      if($oredstatus == 0){
        $redTpe = "oneRed";//金币
        $redCount = 1;
      }else{
         $redTpe = "gold";//金币
         $redCount =  Gold::getUnsetGoldCount($this->user_id);
      }
      $return=[
            "readType"=>$redTpe,//红包类型（新手1元提现红包，金币待入账）
            "redMsg"=>$redCount,//红包消息
            "newNews"=>false,//是否有新消息
            "unTisk"=>$tiskMsg>1?false:true,//是否存在任务（签到，开启宝箱）
            //"unTisk"=>true,//是否存在任务（签到，开启宝箱）
      ];
      return  out($return);
    }
  
    /*
     * 用户领取红包
     */
    public function receiveRedEnvelopes()
    {
        //未领取的金币的数量
        $count = Gold::getUnsetGoldCount($this->user_id);
        //领取金币
        $data = Gold::giveOutGold($this->user_id, $count);

        return out($data);
    }

    /*
     * 用户金币明细接口
     */
    public function goldDetail(Request $request)
    {
        $req = $request->param();
        $page = input("page/d",1);
        $pageSize =input("pageSize/d",15);
        $offset = ($page- 1)*$pageSize;
        $record  = GoldProductRecord::where(['user_id'=>$this->user_id,"status"=>2])
                    ->field("id,title,gold_tribute,update_time")
                    ->order("update_time DESC,id DESC")
                    ->limit($offset,$pageSize)
                    ->select();
        $userInfo = $this->userInfo;
        if(!empty($record)){
            foreach ($record as $k => $v) {
            $record[$k]['update_time'] = date('Y-m-d H:i:s', $v['update_time']);
          } 
        }else{
          $record = [];
        }
        return out(['golb_record' => $record, 'user_gold' => $userInfo['gold_flag'], 'user_balance' => $userInfo['balance']]);
    }

    /*
     * 提现记录 即支出接口
     */
    public function withdrawalsRecord(Request $request)
    {
        $req = $request->param();
        $this->validate($req, [
            'page' => 'integer|>:0',
            'pageSize' => 'integer|>:0',
        ]);

        $page = !empty($req['page']) ? $req['page'] : 1;
        $pageSize = !empty($req['pageSize']) ? $req['pageSize'] : 15;
        $where = "user_id = ".$this->user_id." AND ((state = 1 AND type = 1) OR (type = 2 AND state <> 2) OR ( type = 2 AND state = 2 AND examine_status = 3))";
//        $where = "user_id = ".$this->user_id;
        $withdrawalsRecord = Db::name("user_cash_record")->where($where)->order("id desc")->page($page, $pageSize)->field('order_number as order_no,desc,amount as money,pay_time as wechat_pay_time,create_time,type,state,examine_status as examine,reason')->select();

        foreach ($withdrawalsRecord as $k => &$v) {
            if($v['type'] == 1 ){
               $v['wechat_pay_date'] = date('Y-m-d H:i:s', $v['wechat_pay_time']);
             }else if($v['type'] == 2 && $v['state'] == 1){
                $v['wechat_pay_date'] =date('Y-m-d H:i:s', $v['wechat_pay_time']);
             }else if($v['type'] == 2 && $v['state'] == 2 && $v['examine'] == 3){
                $v['wechat_pay_date'] =date('Y-m-d H:i:s', $v['create_time']);
                 $v['desc'] =$v['desc']."-failed：".$v['reason'];
             }else{
                 $v['wechat_pay_date'] =date('Y-m-d H:i:s', $v['create_time']);
                 $v['desc'] =$v['desc']."-Checking";
             }
             unset($v['reason']);
             unset($v['examine']);
            //分转化为元
            $v['money'] = numBit($v['money']);
           // $v->money = $v['money']/100;
        }

        return out($withdrawalsRecord);
    }

    /**
     * 添加用户信息
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function setUserMsg(){
        $device_tokens = input("device_tokens","");
        $lat = input("lat","");
        $lng = input("lng","");
        $markId = input("post.markId","");
        $headImage = input('post.headImg',"");
        $nickname = input('post.nickname',"");
        $sex = input('post.sex',"");
        $signature = input('post.signature',"");
        $birthday = trim(input('post.birthday',""));

        $up = [];
        if($headImage){
            $service = new Upload();
            $imgUrl = $service->saveTempImage($headImage,$markId);
            if($imgUrl){
                $up['headimg'] = $imgUrl;
            }
        }
        if($nickname){
            $up['nickname'] = $nickname;
        }

        if($sex){
            $up['sex'] = $sex;
        }
        if($signature){
            $up['signature'] = $signature;
        }
        if($birthday){
            $up['birthday'] = $birthday;
            $up['age'] = intval((time() - strtotime($birthday)) / (365 * 24 * 3600));
        }

        if(!empty($device_tokens)){
          $up['device_tokens'] = $device_tokens;
        }
        if((empty($lat) && !empty($lng)) || (!empty($lat) && empty($lng))){
            return out("",10001,"错误请求");
        }
        if(!empty($lat) && !empty($lng)){
            $up['lat'] = $lat;
            $up['lng'] = $lng;
        }
        if(!empty($up)){
            $up['last_login_time'] = time();
        }
        if(!empty($up) && $this->userModel->where(['c_user_id'=>$this->user_id])->update($up) !== false){
            return out();
        }else{
            return out("",10001,"操作失败");
        }


    }

    function bind_paypal()
    {
        $userInfo = &$this->userInfo;

        $paypal = input('paypal','');

        if (empty($paypal))
        {
            return out([],10001,'paypal account couldn\'t be empty  ');
        }

        if ($this->login_flag!=true)
        {
            return out([],10001,'must sign up');
        }

        $userModel = $this->userModel;
        $userModel::update(['paypal_mail'=>$paypal],['c_user_id'=>$userInfo['c_user_id']]);

        return out([],200,'update profile success');
    }


    //申请代理接口
    public function applyRetail()
    {
        $params = input('post.');
        if(empty($params['name'])){
            return out([],10002,'Please input your name.');
        }
        if(empty($params['facebook'])){
            return out([],10002,'Please enter your Facebook account.');
        }
        if(empty($params['nationality'])){
            return out([],10002,'Please fill in your nationality.');
        }
        if(empty($params['address'])){
            return out([],10002,'Please fill in your detailed address.');
        }
        if(empty($params['real_id'])){
            return out([],10002,'Please fill in your ID card.');
        }

        $service = new RetailService();

        //检测是否可以提交申请
        $res = $service->checkApply($params['facebook'],$params['real_id']);
        if(!$res){
            return out([],10002,'Your application has been submitted.');
        }

        $addData['user_id'] = $this->user_id;
        $addData['create_time'] = time();
        $addData['update_time'] = time();
        $addData['facebook'] = $params['facebook'];
        $addData['real_id'] = $params['real_id'];
        $addData['nationality'] = $params['nationality'];
        $addData['address'] = $params['address'];
        $addData['name'] = $params['name'];

        $res = $service->addApply($addData);
        if(!$res){
            return out([],10002,'Failure to submit application');
        }

        return out([],10002,'Application submitted successfully, waiting for contact.');
    }



    /**
     * 获取我的点赞视频
     */
    public function getMyTagsVideo(){

        $user_id = $this->user_id;
        $params = $this->params;
        $other_id = $params['other_id']??$user_id;
        $page = $params['page']??1;
        $pageSize = $params['page_size']??20;

        $id = $other_id;

        //获取点赞视频
        $where = [];
        $where['status'] = 1;
        $where['c_user_id'] = $id;

        $model = new UserVideoLike();
        $data = $model->getUserTagsVideoList($where,$page,$pageSize);

        $videoModel = new UserVideoLogic();
        $videoService = new UserVideoService();

        if($data){

            $video = [];//视频数据
            $video_id = [];
            $du_video_id = [];
            //获取视频信息
            foreach($data as $val){//查询视频信息

                if($val['du_type'] == 1){//真实源

                    $video_id[] = $val['a_v_id'];

                }else if($val['du_type'] == 2){

                    $du_video_id[] = $val['a_v_id'];
                }
            }

            if($video_id){

                $real_res_data = $videoModel->getListByCondition(['aliyun_video_id'=>['in',$video_id]]);

                if($real_res_data){

                    foreach($real_res_data as $key => $val){
                        $real_res_data[$key] = $videoService->initVideo($val);
                    }
                }
            }
            $video = array_merge($video,$real_res_data??[]);

            if($du_video_id){

                $du_res_data = $videoModel->getDuListByCondition(['id'=>['in',$du_video_id]]);

                if($du_res_data){

                    foreach($du_res_data as $key => $val){
                        $du_res_data[$key]['user_id'] = $val['du_id'];
                        $du_res_data[$key]['du_type'] = 2;//虚拟视频标识

                    }
                    $du_res_data = collection($du_res_data)->toArray();
                }
            }
            $video = array_merge($video,$du_res_data??[]);

            return out($video,200,'Successful operation');

        }else{
            return out([],200,'Sorry! This category have nothing data');
        }
    }






}
