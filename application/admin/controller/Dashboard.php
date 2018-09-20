<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\model\DownloadChannel;
use app\model\FConfig;
use app\model\InvitationCodeShare;
use app\model\News;
use app\model\NewsVisit;
use app\model\NewVideoShare;
use app\model\UserApprentice;
use think\Db;
use app\model\User;
use app\model\VideoVisit;
use think\Request;
use time\TimeUtil;
use app\model\GoldProductRecord;
use app\model\NewVideo as Video;
/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
//class Dashboard //extends Backend
{
    protected $relationSearch = false;
    /**
     * 查看
     */
    public function index()
    {
//
//        $seventtime = \fast\Date::unixtime('day', -7);
//        $paylist = $createlist = [];
//        for ($i = 0; $i < 7; $i++)
//        {
//            $day = date("Y-m-d", $seventtime + ($i * 86400));
//            $createlist[$day] = mt_rand(20, 200);
//            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
//        }
//        $hooks = config('addons.hooks');
//        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
//        //总用户数
//        $user_total = Db::name("user")->count("id");
//        //今日新增用户
//        $today_add_user = Db::name('user')->whereTime('create_time', 'today')->count('id');
//        //今日登陆用户
//        $today_login_user = Db::name('user')->where(function ($query) {
//                $query->whereTime('update_time', 'today');
//        })->whereOr(function ($query) {
//                $query->whereTime('last_login_time', 'today');
//        })->count();
//        //今日新手提现金额1元金额
//        /*
//            $today_new_cash_success_money = Db::name('user_cash_record')->where(['state'=>1,'type'=>1])->whereTime('pay_time','today')->sum('amount');
//        */
//        $today_new_cash_success_money = 0;
//        //今日其他提现申请金额
//            $today_examine_cash_money = Db::name('user_cash_record')->where(['state'=>0,'type'=>2])->whereTime('pay_time','today')->sum('amount');
//        //今日已成功提现金额
//            $today_cash_success_money = Db::name('user_cash_record')->where(['state'=>1,'type'=>1])->whereTime('pay_time','today')->sum('amount');
//        //总提现金额
//            $today_cash_all_money = $today_new_cash_success_money+$today_cash_success_money;
//        //已提现总金额
//            $cash_all_money =  Db::name('user_cash_record')->where(['state'=>1])->sum('amount');
//        //总视频数量
//            $video_count = Db::name("video")->count("id");
//        //今日添加数量
//            $video_add_count = Db::name("video")->whereTime('order_time',"today")->count("id");
//        //今日观看视频数量
//            $video_visit_count = Db::name("video_visit")->whereTime('create_time','today')->sum("count");
//        //获取金币最多用户前10位
//            /*
//            $getMaxgold = Db::name("gold_product_record")
//                    ->alias("a")
//                    ->join("__USER__ u","u.c_user_id = a.user_id")
//                    ->whereTime("a.update_time","today")
//                    ->group("a.user_id")
//                    ->field("sum(a.gold_tribute) as gold,a.user_id,u.nickname")
//                    ->order("gold DESC")
//                    ->limit(0,10)->select();
//            */
//        //获得金币最多用户
//        /*
//            $max_money = Db::name("user")
//                        ->limit(10)
//                        ->where(['status'=>1])
//                        ->order("total_balance DESC")
//                        ->field("c_user_id,nickname,total_balance,total_gold_flag,create_time")
//                        ->select();
//        */
//                //pp($max_money);die;
//        $this->view->assign([
//            'user_total'        => $user_total,
//            'today_add_user'    => $today_add_user,
//            'today_login_user' => $today_login_user,
//            'cash_all_money'   => $cash_all_money,
//            'today_new_cash_success_money'   =>$today_new_cash_success_money,
//            'today_examine_cash_money'  => $today_examine_cash_money,
//            'today_cash_success_money'       => $today_cash_success_money,
//            'today_cash_all_money'    => $today_cash_all_money,
//            'video_count'         =>$video_count,
//            'video_add_count'         => $video_add_count,
//            'video_visit_count'          => $video_visit_count,
//            'createlist'       => $createlist,
//            'uploadmode'       => $uploadmode,
//            "getMaxgold"=>$getMaxgold = [],
//            "max_money"=>$max_money=0,
//        ]);

        return $this->view->fetch();
    }
    /**
     * 数据报表
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function getcount(){
        /*
        $time = input("time");
        $endTime =strtotime(date("Y-m-d")." ".$time);
        $startTime =$endTime-60;
        $User= new User();
        $VideoVisit = new VideoVisit();
        $reg_count = $User::where('create_time','between time',[$startTime,$endTime])->count('id');
        $login_count = $User->where('update_time','between time',[$startTime,$endTime])->whereOr('last_login_time','between time',[$startTime,$endTime])->count("id");
        $visit_count = $VideoVisit->where('create_time','between time',[$startTime,$endTime])->count('id');
        return json(['reg_count'=>$reg_count,"login_count"=>$login_count,"visit_count"=>$visit_count]);
    */

    }
    /**
     * 作用
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function user(){
        if ($this->request->isAjax())
        {
             $this->relationSearch = true;
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $this->model =  new GoldProductRecord();
            //$count = Db::
            $filter = input("filter");
            if(strlen($filter)==2){

                $time =TimeUtil::today();
                $where=[
                    "a.update_time"=> ['between time',[$time[0],$time[1]]],
                ];
            }
            $time =TimeUtil::today();
            $total = $this->model
                    ->alias("a")
                    ->where($where)
                    ->group("a.user_id")
                    ->count();
            $list = $this->model
                    ->alias("a")
                    ->join("__USER__ u","a.user_id = u.c_user_id","LEFT")
                    ->field("a.id,a.update_time as update_time,a.user_id,sum(a.gold_tribute) as now_gold_tribute ,u.gold_flag,u.total_balance,u.balance,u.total_gold_flag, u.nickname,u.status,u.create_time")
                    ->where($where)
                    ->group("a.user_id")
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
                

            //echo $this->model->getLastSql();die;
            foreach ($list as $key => &$value) {
                $value['details_url'] = "/admin/user/user/index?c_user_id=".$value['user_id'];
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        //总用户数
        $user_total = Db::name("user")->count("id");
        $temp_user_total = Db::name("temp_user")->count("id");
        //今日新增用户
        $today_add_user = Db::name('user')->whereTime('create_time', 'today')->count('id');
        $temp_today_add_user = Db::name('temp_user')->whereTime('create_time', 'today')->count('id');
        //今日登陆用户
        $today_login_user = Db::name('user')->where(function ($query) {
                $query->whereTime('update_time', 'today');
        })->whereOr(function ($query) {
                $query->whereTime('last_login_time', 'today');
        })->count();
        //
        $temp_today_login_user = Db::name('temp_user')->where(function ($query) {
                $query->whereTime('update_time', 'today');
        })->whereOr(function ($query) {
                $query->whereTime('last_login_time', 'today');
        })->count();

        // 今日用户产生的金币数量

        $todayUserProductGold = GoldProductRecord
            ::where('create_time','between',[
                mktime(0,0,0),mktime(23,59,59)
            ])
            ->sum('gold_tribute');

        $exchangeRate = FConfig
            ::where('name','eq','exchange_rate')
            ->value('value');

        // 今日用户产生的金币数量
        $todayUserProductBalance = sprintf( '%.2f',  $todayUserProductGold / $exchangeRate / 100 * 7   ) ;
        $this->assign('today_user_product_gold',$todayUserProductGold);
        $this->assign('today_user_product_balance',$todayUserProductBalance);


        //获取金币最多用户前10位
        
        $getMaxgold = Db::name("gold_product_record")
                    ->alias("a")
                    ->join("__USER__ u","u.c_user_id = a.user_id")
                    ->whereTime("a.update_time","today")
                    ->group("a.user_id")
                    ->field("sum(a.gold_tribute) as gold,a.user_id,u.nickname")
                    ->order("gold DESC")
                    ->limit(0,10)
                    ->select();



        $this->view->assign([
            'user_total'        => $user_total,
            'today_add_user'    => $today_add_user,
            'today_login_user' => $today_login_user,
            'temp_user_total'        => $temp_user_total,
            'temp_today_add_user'    => $temp_today_add_user,
            'temp_today_login_user' => $temp_today_login_user,
           // 'reg_column'=>($today_add_user/($today_add_user+$temp_today_add_user))*100,
            "getMaxgold"=>$getMaxgold,
        ]);
        return $this->view->fetch();
    }
    /**
     * 用户报表
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function userreport(){
        $type = input("type","");
        if($type  == "self"){
            $time = input("time","");
            if(empty($time)){
                return json(['code'=>-1,"msg"=>"请选择查询时间"]);
            }
            $time = explode("/",$time);
            if(count($time)<2){
                  return json(['code'=>-1,"msg"=>"查询时间格式错误1"]);
            }
            $time_arr =$this->create_time($time[0],$time[1]);
            if($time_arr['error'] == -1){
                return json(['code'=>-1,"msg"=>"查询时间格式错误2"]);
            }
            $time['0'] =strtotime($time[0]);
            $time['1'] =strtotime($time[1]);
        }else{
            $time =TimeUtil::today();
            $time_arr =$this->create_time(date('Y-m-d H:i:s',$time[0]),date('Y-m-d H:i:s',$time[1]));

            foreach ($time_arr['data'] as $index=>$datum)
            {
                $time_arr['data'][$index] = date($time_arr['date_st'],$time[0] + 3600 * $index);
            }

        }


        $date_sl = $time_arr['date_sl'];
        //今日金币
        $goldData = Db::name("gold_product_record")
            ->where(['create_time'=>['between',[$time[0],$time[1]]]])
            ->field("FROM_UNIXTIME(create_time,'$date_sl') as day,sum(gold_tribute) AS count")
            ->group("day DESC")
            ->select();

        //真实新用户
        $data = Db::name("user")
                    ->where(['create_time'=>['between',[$time[0],$time[1]]]])
                    ->field("FROM_UNIXTIME(create_time,'$date_sl') as day,count(id) as count")
                    ->group("day DESC")
                    ->select();

        //真实登录用户
        $login = Db::name("user")
                    ->where(['update_time'=>['between',[$time[0],$time[1]]]])
                    ->whereOR(['last_login_time'=>['between',[$time[0],$time[1]]]])
                    ->field("FROM_UNIXTIME(last_login_time,'$date_sl') as day,count(id) as count")
                    ->group("day DESC")
                    ->select();

        //临时
        $temp_data = Db::name("temp_user")
                    ->where(['create_time'=>['between',[$time[0],$time[1]]]])
                    ->field("FROM_UNIXTIME(create_time,'$date_sl') as day,count(id) as count")
                    ->group("day DESC")
                    ->select();

        //登录
        $temp_login = Db::name("temp_user")
                    ->where(['update_time'=>['between',[$time[0],$time[1]]]])
                    ->whereOR(['last_login_time'=>['between',[$time[0],$time[1]]]])
                    ->field("FROM_UNIXTIME(last_login_time,'$date_sl') as day,count(id) as count")
                    ->group("day DESC")
                    ->select();

           
        $list=[];
        $gold_count = [];
        $reg_count = [];
        $temp_reg_count = [];
        $login_count = [];
        $temp_login_count = [];
        sort($time_arr['data']);

        foreach ($time_arr['data'] as $key => $value) {
            $b['time'] = $value;
            $temp_gold_count = 0;
            $count = 0;
            $temp_count = 0;
            $lcount =0;
            $temp_lcount =0;
            foreach ($goldData as $k => $v) {
                if($value == $v['day']){
                    $temp_gold_count = $v['count'];
                    unset($goldData[$k]);
                    break;
                }
            }
            foreach ($data as $k => $v) {
                if($value == $v['day']){
                    $count = $v['count'];
                    unset($data[$k]);
                    break;
                }
            }
            foreach ($temp_data as $q => $w) {
                if($value == $w['day']){
                    $temp_count = $w['count'];
                    unset($temp_data[$q]);
                    break;
                }
            }
            foreach ($login as $e => $ve) {
                if($value == $ve['day']){
                    $lcount = $ve['count'];
                    unset($login[$e]);
                    break;
                }
            }
            foreach ($temp_login as $r => $y) {
                if($value == $y['day']){
                    $temp_lcount = $y['count'];
                    unset($temp_login[$r]);
                    break;
                }
            }
            $gold_count[] = $temp_gold_count/1000;
            $reg_count[] = $count;
            $temp_reg_count[] = $temp_count;
            $login_count[]=$lcount;
            $temp_login_count[]=$temp_lcount;
        }


        return json(['code'=>1,'gold_count'=>$gold_count,'time'=>$time_arr['data'],'reg_count'=>$reg_count,'login_count'=>$login_count,"temp_reg_count"=>$temp_reg_count,'temp_login_count'=>$temp_login_count]);

    }
    public function create_time($start_time,$end_time){
        $s_time = strtotime($start_time);
        $e_time = strtotime($end_time);
        if($s_time >= $e_time){
            return ['error'=>-1];
        }
        $date1=date_create($start_time);
        $date2=date_create($end_time);
        $diff=date_diff($date1,$date2);
        $date_flag = "";
        $date_diff = 0;
        $date_array = ['y',"m",'d','h'];
        foreach ($diff as $key => $value) {
            if($value > 0 && in_array($key, $date_array)){
                $date_flag = strtoupper($key);
                $date_diff = $value;
                break;
            }
        }
        $a=[];
        $b=[];
        foreach ($date_array as $key => $value) {
            $date_array[$key] = strtoupper($value);
        }
        $date_ =str_replace($date_array,['year','month','day','hour'], $date_flag);
        $date_st =str_replace($date_array,['Y','Y-m','Y-m-d','Y-m-d H'], $date_flag);
        $date_sl =str_replace($date_array,['%Y','%Y-%m','%Y-%m-%d','%Y-%m-%d %H'], $date_flag);
        for ($i=0;$i<=$date_diff; $i++) {
            $a[] = date($date_st,strtotime("-".$i." ".$date_));
        }
        $b['diff'] = $date_diff;
        $b['error'] = 1;
        $b['date_flag'] = $date_flag;
        $b['date_st'] = $date_st;
        $b['date_sl'] = $date_sl;
        $b['data'] = $a;
        return $b;
    }

    /**
     *视频
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function video(){

        $this->model  = new VideoVisit();
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $r = json_encode([]);
            $filter = input("filter",$r);
            $filter = json_decode($filter,true);
            $map=[];
            $where=[];
            $where['create_time'] = ['between time',[strtotime(date('Y-m')),time()]];
            if(!empty($filter)){
                foreach ($filter as $key => $value) {
                    if($key == "search_time"){
                        $time = explode("-", $value);
                        $s_time = $time[0]."-".$time[1]."-".$time[2];
                        $e_time = $time[3]."-".$time[4]."-".$time[5];
                        $start_time = strtotime($s_time);
//                        if($start_time < strtotime(date('Y-m'))){
//                            $start_time = strtotime(date('Y-m'));
//                        }
                        $where['create_time'] =['between time',[$start_time,strtotime($e_time)]];
                    }
                }
            }

            $total = $this->model
                ->group("video_id")
                ->where($where)
                ->count();
            $list = $this->model
                ->group("video_id")
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            //echo $this->model->getLastSql();die();
            $videoModel = new Video();
            foreach ($list as $key => &$value) {
//                $value['title'] = $videoModel->where(['id'=>$value['video_id']])->value('title');
                $value['title'] = "采集视频".$value['video_id'];
//                $value['viewing_number']= count($this->model->where(['video_id'=>$value['video_id']])->group("user_id")->select());
                $value['viewing_number'] = "??";
//                $value['play_count'] = $this->model->where(['video_id'=>$value['video_id']])->count();
                $value['play_count'] = "??";

            }
            $result = array(
                "total" => $total,
                "rows" => $list,
            );
            return json($result);
        }



        $this->getvideo();
        return $this->view->fetch();
    }
    /**
     * 获取视频信息
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function getvideo(){
        //总视频数量
        $videoModel = new Video();
        $this->model = new VideoVisit();
        $map=[];
        $vmap=[];
        $time = input("time","");

        //月播放总量
        $map['create_time'] = ['between time',[strtotime(date('Y-m')),time()]];
        if(!empty($time)){
            $time = explode("-", $time);
            $s_time = $time[0]."-".$time[1]."-".$time[2];
            $e_time = $time[3]."-".$time[4]."-".$time[5];
            $start_time = strtotime($s_time);
//            if($start_time < strtotime(date('Y-m'))){
//                $start_time = strtotime(date('Y-m'));
//            }
            $map['create_time'] = ['between time',[$start_time,strtotime($e_time)]];
            $vmap['order_time'] = ['between time',[strtotime($s_time),strtotime($e_time)]];
        }


        //总视频
        $video_count = $videoModel->where($vmap)->count("id");

        //视频播放总次数
        $all_paly_count = $this->model->where($map)->sum("count");
        //播放人数
//        $viewing_number = $this->model->where($map)->count("user_id");
        $viewing_number = $this->model->group("user_id")->where($map)->count();
        $return = [
            "video_count"=>$video_count,
            "all_paly_count"=>$all_paly_count,
            "viewing_number"=>$viewing_number,
        ];
        if ($this->request->isAjax()){
            return json($return);
        }else{
            $this->assign("data",$return);
        }
    }

    /**
     * 新闻
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function news(){

        $this->model  = new NewsVisit();
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $r = json_encode([]);
            $filter = input("filter",$r);
            $filter = json_decode($filter,true);
            $map=[];
            $where=[];
            $where['create_time'] = ['between time',[strtotime(date('Y-m')),time()]];
            if(!empty($filter)){
                foreach ($filter as $key => $value) {
                    if($key == "search_time"){
                        $time = explode("-", $value);
                        $s_time = $time[0]."-".$time[1]."-".$time[2];
                        $e_time = $time[3]."-".$time[4]."-".$time[5];
                        $start_time = strtotime($s_time);
//                        if($start_time < strtotime(date('Y-m'))){
//                            $start_time = strtotime(date('Y-m'));
//                        }
                        $where['create_time'] =['between time',[$start_time,strtotime($e_time)]];
                    }
                }
            }
            $total = $this->model
                ->group("news_id")
                ->where($where)
                ->count();
            $list = $this->model
                ->group("news_id")
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            //echo $this->model->getLastSql();die();
            $newsModel = new News();
            foreach ($list as $key => &$value) {
                $value['title'] = $newsModel->where(['id'=>$value['news_id']])->value('title');
                $value['viewing_number']= count($this->model->where(['news_id'=>$value['news_id']])->group("user_id")->select());
                $value['play_count'] = $this->model->where(['news_id'=>$value['news_id']])->count();

            }
            $result = array(
                "total" => $total,
                "rows" => $list,
            );
            return json($result);
        }



        $this->getnews();
        return $this->view->fetch();
    }


    /**
     * 新闻
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function getnews(){
        //总视频数量
        $newsModel = new News();
        $this->model = new NewsVisit();
        $map=[];
        $vmap=[];
        $time = input("time","");

        //月播放总量
        $map['create_time'] = ['between time',[strtotime(date('Y-m')),time()]];
        if(!empty($time)){
            $time = explode("-", $time);
            $s_time = $time[0]."-".$time[1]."-".$time[2];
            $e_time = $time[3]."-".$time[4]."-".$time[5];
            $start_time = strtotime($s_time);
//            if($start_time < strtotime(date('Y-m'))){
//                $start_time = strtotime(date('Y-m'));
//            }
            $map['create_time'] = ['between time',[$start_time,strtotime($e_time)]];
        }

        //总视频
        $news_count = $newsModel->where($map)->count("id");

        //视频播放总次数
        $all_paly_count = $this->model->where($map)->count("id");
        //播放人数
        $viewing_number = $this->model->group("user_id")->where($map)->count();
        $return = [
            "news_count"=>$news_count,
            "all_paly_count"=>$all_paly_count,
            "viewing_number"=>$viewing_number,
        ];
        if ($this->request->isAjax()){
            return json($return);
        }else{
            $this->assign("data",$return);
        }
    }


    /**
     * 提现
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function cash(){


          $model= Db::name('user_cash_record');
      
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $this->model = &$model;
            $filter = input("filter");
            $where ="";
            $where= "a.state = 2 and examine_status = 2";
            //$where= "1 = 1";

            if(strlen($filter)==2){
                $time =TimeUtil::today();
               //$where.= " AND  a.create_time between ".$time[0]. "  and  ".$time[1];
              
            }else{
                //对时间处理
               
                $filter = json_decode(input("filter"),true);
                $time = explode("-", $filter['create_time']);
               // echo  strtotime($time[0]."-".$time[1]."-".$time[2]);die;
                $start_time = strtotime($time[0]."-".$time[1]."-".$time[2]);
                $end_time = strtotime($time[3]."-".$time[4]."-".$time[5]);
                //$where['a.create_time']=['between time',$start_time,$end_time];
                 $where.= " AND  a.create_time between ".$start_time. "  and  ".$end_time;

            }
           
            $time =TimeUtil::today();
            $total = $this->model
                    ->alias("a")
                    ->where($where)
                    ->group("a.user_id")
                    ->count();
            
            $list = $this->model
                    ->alias("a")
                    ->join("__USER__ u","a.user_id = u.c_user_id","LEFT")
                    ->field("a.id,a.create_time,a.user_id,sum(a.amount) as cash_amount,u.gold_flag,u.total_balance,u.balance,u.total_gold_flag, u.nickname,u.status,u.create_time as reg_time,u.redcash,u.oredstatus")
                    ->where($where)
                    ->group("a.user_id")
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            //echo $this->model->getLastSql();die;

            foreach ($list as $key => &$value) {
                $value['onered_status'] = $value['oredstatus'] == 1?"是 | ":"否 | ";
                $value['onered_status'] .= $value['redcash'] ==1 ?"是":"否";
                //$value['details_url'] = "/admin/user/user/index?c_user_id=".$value['user_id'];
                //查询
                $value['examine_cash_amount'] = $this->model->where(['state'=>2,'type'=>2,'user_id'=>$value['user_id']])->sum("amount");

            }
            $result = array("total" => $total, "rows" => $list);
           
            return json($result);
        }
        $this->searchcash();
        return $this->view->fetch();

    }

    /**
     * 地址列表
     *----------------------------------------
     * @param  string null
     *----------------------------------------
     * @author ll
     * @access public
     * @return void
     */
    public function searchcash(){
        $model= Db::name('user_cash_record');


        $time = input("time","");
        if(!empty($time)){
            //pp($time);die;
          
            $time = explode("-", $time);
            // echo  strtotime($time[0]."-".$time[1]."-".$time[2]);die;
            $start_time = strtotime($time[0]."-".$time[1]."-".$time[2]);
            $end_time = strtotime($time[3]."-".$time[4]."-".$time[5]);
            //$where['a.create_time']=['between time',$start_time,$end_time];
            $where= "create_time between ".$start_time. "  and  ".$end_time;
            //已提现总金额
            $cash_all_money =  $model->where(['state'=>2,'examine_status'=>2])->where($where)->sum('amount');
//            今日新手提现金额1元金额
            $today_new_cash_success_money =$model->where(['state'=>1,'type'=>1])->where($where)->sum('amount');
            //今日其他提现申请金额
            $today_examine_cash_money = $model->where(['state'=>0,'type'=>2])->where($where)->sum('amount');
            //今日已成功提现金额
            $today_cash_success_money = $model->where(['state'=>2,'type'=>2,'examine_status'=>2])->where($where)->sum('amount');
            //总提现金额
            $today_cash_all_money = $today_cash_success_money;
            
        }else{
            //已提现总金额
            $cash_all_money =  $model->where(['state'=>2,'examine_status'=>2])->sum('amount');
            //今日新手提现金额1元金额
            $today_new_cash_success_money =$model->where(['state'=>1,'type'=>1])->sum('amount');
            //今日其他提现申请金额
            $today_examine_cash_money = $model->where(['state'=>0,'type'=>2])->sum('amount');
            //今日已成功提现金额
            $today_cash_success_money = $model->where(['state'=>2,'type'=>2,'examine_status'=>2])->sum('amount');
            //总提现金额
            $today_cash_all_money = $today_cash_success_money;
        }
         //用户剩余提现总金额
        $all_user_balance = Db::name("user")->sum("balance");
        $all_user_gold_flag = Db::name("user")->sum("gold_flag");

        $data = [
            'cash_all_money'   => $cash_all_money,
            'today_new_cash_success_money'   =>$today_new_cash_success_money,
            'today_examine_cash_money'  => $today_examine_cash_money,
            'today_cash_success_money' => $today_cash_success_money,
            'today_cash_all_money'    => $today_cash_all_money,
            "all_user_balance"=>$all_user_balance,
            "all_user_gold_flag"=>$all_user_gold_flag
        ];
        if ($this->request->isAjax()){
            return json($data);
        }else{
            $this->view->assign([
                'cash_all_money'   => $cash_all_money,
                'today_new_cash_success_money'   =>$today_new_cash_success_money,
                'today_examine_cash_money'  => $today_examine_cash_money,
                'today_cash_success_money'       => $today_cash_success_money,
                'today_cash_all_money'    => $today_cash_all_money,
                "all_user_balance"=>$all_user_balance,
                "all_user_gold_flag"=>$all_user_gold_flag
            ]);
        }
     
    }


    /**
     * 渠道
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function downloadChannel(){

        $this->model  = new DownloadChannel();

        if ( $this->request->isAjax() )
        {

            $downloadJson = $this->echartData();

            $startStamp = mktime(0,0,0);
            $endStamp = mktime(23,59,59);

            $mark = '%Y-%m-%d';
            $dateFormat = 'Y-m-d';
            $loopCount = 24;
            $timeStampIncrement = 3600;

            $time = input('time','');
            $this->splitData($time
                ,$startStamp
                ,$endStamp
                ,$downloadJson
                ,$mark
                ,$dateFormat
                ,$loopCount
                ,$timeStampIncrement);

            $download = $this
                ->model
                ->where('create_time','between',[$startStamp,$endStamp])
                ->group("channel,from_unixtime(create_time,'{$mark}')")
                ->field("case channel
    when 'invitation_code' then '邀请码'
    when 'twitter share_video' then 'twitter视频分享'
	when 'utm_source=google-play&utm_medium=organic' then '商店安装'
	when 'utm_source=(not%20set)&utm_medium=(not%20set)' then '未知/未获取到/无源'
else '其他源' end as channel
                 ,count(1) as count,FROM_UNIXTIME(create_time, '{$mark}') mark")
                ->select()
            ;

            $this->fillData(
                $downloadJson
                ,$download
                ,$loopCount
                ,$dateFormat
                ,$startStamp
                ,$timeStampIncrement);

            return json($downloadJson);

        }


        $this->getDownloadChannel();
        return $this->view->fetch();
    }

    /**
     * 渠道
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function getDownloadChannel(){
        //总视频数量
        $this->model = new DownloadChannel();
        $map=['create_time'=>['between time',[mktime(0,0,0),mktime(23,59,59)]]];
        $vmap=[];
        $time = input("time","");
        if(!empty($time)){
            $time = explode("-", $time);
            $s_time = $time[0]."-".$time[1]."-".$time[2];
            $e_time = $time[3]."-".$time[4]."-".$time[5];
            $map['create_time'] = ['between time',[strtotime($s_time),strtotime($e_time)]];
            $vmap['order_time'] = ['between time',[strtotime($s_time),strtotime($e_time)]];
        }

        //总视频
        $download_count = $this->model->count("id");

        //视频播放总次数
        $all_paly_count = $this->model->where($map)->count("id");

        $return = [
            "download_count"=>$download_count,
            "today_download_count"=>$all_paly_count,
        ];
        if ($this->request->isAjax()){
            return json($return);
        }else{
            $this->assign("data",$return);
        }
    }

    public function videoShare()
    {
        $this->model  = new NewVideoShare();

        if ( $this->request->isAjax() )
        {

            $downloadJson = $this->echartData();

            $startStamp = mktime(0,0,0);
            $endStamp = mktime(23,59,59);

            $mark = '%Y-%m-%d';
            $dateFormat = 'Y-m-d';
            $loopCount = 24;
            $timeStampIncrement = 3600;

            $time = input('time','');

            $this->splitData($time
                ,$startStamp
                ,$endStamp
                ,$downloadJson
                ,$mark
                ,$dateFormat
                ,$loopCount
                ,$timeStampIncrement);

            $download = $this
                ->model
                ->where('create_time','between',[$startStamp,$endStamp])
                ->group("channel,from_unixtime(create_time,'{$mark}')")
                ->field("case channel
    when 'facebook' then 'facebook视频分享'
    when 'twitter' then 'twitter视频分享'
	when 'linkin' then 'linkedin视频分享'
else '其他源' end as channel
                 ,count(1) as count,FROM_UNIXTIME(create_time, '{$mark}') mark")
                ->select()
            ;

            $this->fillData(
                $downloadJson
                ,$download
                ,$loopCount
                ,$dateFormat
                ,$startStamp
                ,$timeStampIncrement);

            return json($downloadJson);

        }


        $this->getVideoShare();
        return $this->view->fetch();
    }

    /**
     * 渠道
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function getVideoShare(){
        //总视频数量
        $this->model = new NewVideoShare();
        $map=['create_time'=>['between time',[mktime(0,0,0),mktime(23,59,59)]]];
        $time = input("time","");
        if(!empty($time)){
            $time = explode("-", $time);
            $s_time = $time[0]."-".$time[1]."-".$time[2];
            $e_time = $time[3]."-".$time[4]."-".$time[5];
            $map['create_time'] = ['between time',[strtotime($s_time),strtotime($e_time)]];
        }

        //总视频
        $download_count = $this->model->count("id");
        //视频播放总次数
        $all_paly_count = $this->model->where($map)->count("id");

        $return = [
            "share_count"=>$download_count,
            "today_share_count"=>$all_paly_count,
        ];

        $this->assign("data",$return);
    }

    public function invitationCode()
    {
        $this->model  = new InvitationCodeShare();

        if ( $this->request->isAjax() )
        {

            $downloadJson = $this->echartData();

            $startStamp = mktime(0,0,0);
            $endStamp = mktime(23,59,59);

            $mark = '%Y-%m-%d';
            $dateFormat = 'Y-m-d';
            $loopCount = 24;
            $timeStampIncrement = 3600;

            $time = input('time','');

            $this->splitData($time
                ,$startStamp
                ,$endStamp
                ,$downloadJson
                ,$mark
                ,$dateFormat
                ,$loopCount
                ,$timeStampIncrement);

            $download = $this
                ->model
                ->where('create_time','between',[$startStamp,$endStamp])
                ->group("channel,from_unixtime(create_time,'{$mark}')")
                ->field("case channel
    when 'facebook' then 'facebook邀请码分享'
    when 'twitter' then 'twitter邀请码分享'
	when 'linkin' then 'linkedin邀请码分享'
else '其他源' end as channel
                 ,count(1) as count,FROM_UNIXTIME(create_time, '{$mark}') mark")
                ->select()
            ;

            $this->fillData(
                $downloadJson
                ,$download
                ,$loopCount
                ,$dateFormat
                ,$startStamp
                ,$timeStampIncrement);

            return json($downloadJson);

        }

        $this->getInvitationCode();
        return $this->view->fetch();
    }

    /**
     * 渠道
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     */
    public function getInvitationCode(){
        //总视频数量
        $this->model = new InvitationCodeShare();
        $map=['create_time'=>['between time',[mktime(0,0,0),mktime(23,59,59)]]];
        $time = input("time","");
        if(!empty($time)){
            $time = explode("-", $time);
            $s_time = $time[0]."-".$time[1]."-".$time[2];
            $e_time = $time[3]."-".$time[4]."-".$time[5];
            $map['create_time'] = ['between time',[strtotime($s_time),strtotime($e_time)]];
        }

        //总视频
        $download_count = $this->model->count("id");
        //视频播放总次数
        $all_paly_count = $this->model->where($map)->count("id");

        $return = [
            "share_count"=>$download_count,
            "today_share_count"=>$all_paly_count,
        ];

        $this->assign("data",$return);
    }

    public function echartData()
    {
        return [
            'xAxis'=>[
                'data'=>[]
            ],
            'legend'=>[
                'data'=>[]
            ],
            'series'=>[

            ]
        ];
    }

    /**
     * 根据 范围时间字符串 返回 返回范围时间戳
     *
     * @param $time string XXXX-XX-XX XX:XX:XX/XXXX-XX-XX XX:XX:XX
     * @param $startStamp
     * @param $endStamp
     * @param $downloadJson
     * @param $mark
     * @param $dateFormat
     * @param $loopCount
     * @param $timeStampIncrement
     */
    private function splitData($time,&$startStamp,&$endStamp,&$downloadJson,&$mark,&$dateFormat,&$loopCount,&$timeStampIncrement)
    {

        if ($time != '')
        {
            $times = explode('/',$time);
            $startStamp = strtotime( $times[0] );
            $endStamp = strtotime( $times[1] );

            $dateDiff = date_diff(
                (new \DateTime())->setTimestamp($startStamp)
                ,(new \DateTime())->setTimestamp($endStamp)
            );

            // 间隔天数
            $diffDays = $dateDiff->d;

            if ( $diffDays >= 1 )
            {
                // 超过一天 如果有小时，分，秒则加一天
                $diffDays += $diffDays > 0 ? ( $dateDiff->h !=0 || $dateDiff->i != 0 || $dateDiff->s != 0 ? 1 : 0 ) : 0;
                $loopCount = $diffDays;

                // 修改增加时间
                $timeStampIncrement = $timeStampIncrement*24;
                for ($i=0;$i<$loopCount;$i++)
                    $downloadJson['xAxis']['data'][] = date($dateFormat,$startStamp+$timeStampIncrement*$i);
            }
            else
            {
                $mark.=' %H';
                $dateFormat.=' H';

                for ($i=0;$i<$loopCount;$i++)
                    $downloadJson['xAxis']['data'][] = date($dateFormat,$startStamp+$timeStampIncrement*$i);
            }
        }
        else
        {
            $mark.=' %H';
            $dateFormat.=' H';

            for ($i=0;$i<$loopCount;$i++)
                $downloadJson['xAxis']['data'][] = date($dateFormat,$startStamp+$timeStampIncrement*$i);
        }

    }

    /**
     * 数据填充
     *
     * @param $downloadJson
     * @param $download
     * @param $loopCount
     * @param $dateFormat
     * @param $startStamp
     * @param $timeStampIncrement
     */
    public function fillData(&$downloadJson,$download,$loopCount,$dateFormat,$startStamp,$timeStampIncrement)
    {
        $tempArray = [];
        //今日下载计算
        if ( !empty($download) )
        {

            $download = collection($download)->toArray();

            // 填充真实数据
            foreach ($download as &$item)
            {
                !isset($tempArray[$item['channel']])
                &&
                $tempArray[ $item['channel'] ] = [
                    'name'=> $item['channel']
                    ,'type'=>'bar'
                    , 'data'=>[]
                ];

                $tempArray
                [ $item['channel'] ]
                ['data']
                [$item['mark']]
                    =
                    $item['count'];

            }

            // 填充归零数据
            foreach ($tempArray as &$channel)
            {
                $channelData = &$channel['data'];
                for ($i=0;$i<$loopCount;$i++)
                {
                    $date = date($dateFormat,$startStamp+$timeStampIncrement*$i);
                    !isset($channelData[ $date ]) && $channelData[ $date ] = 0;
                }
            }

            foreach ($tempArray as &$item)
            {
                ksort($item['data']);

                $item['data'] = array_values( $item['data'] );
            }

            $downloadJson['series'] = array_values($tempArray);
            $downloadJson['legend']['data'] = array_keys( $tempArray );
        }

    }

    /**
     * 收徒统计
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function apprentice(){
        $this->model =  new UserApprentice();
        if ($this->request->isAjax())
        {
            $this->relationSearch = true;
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            //根据where条件
            $u=["c_user_id","create_time"];
            $sort = input("sort");
            if(in_array($sort, $u)){
                $sort = "u.".$sort;
            }else{
                $sort = $sort;
            }
            $r = json_encode([]);
            $filter = input("filter",$r);
            $filter = json_decode($filter,true);
            $where = [];
            $map=[];
            if(!empty($filter)){
                foreach ($filter as $key => $value) {
                    if($key == "search_time"){
                        $time = explode("-", $value);
                        $s_time = $time[0]."-".$time[1]."-".$time[2];
                        $e_time = $time[3]."-".$time[4]."-".$time[5];
                        $map['create_time'] = ['between time',[strtotime($s_time),strtotime($e_time)]];
                        $where['ua.create_time'] =['between time',[strtotime($s_time),strtotime($e_time)]];
                    }
                    if($key == "nickname"){
                        $where['u.nickname'] = ['like',"%{$value}%"];
                    }
                    if($key == "c_user_id"){
                        $where['u.c_user_id'] = $value;
                    }
                }
            }
            //默认查询徒弟个数
            $where['ua.type'] = 1;
            $where['ua.status'] = 1;
            $total = $this->model
                ->alias("ua")
                ->join("__USER__ u","ua.master_user_id = u.c_user_id","LEFT")
                ->group("master_user_id")
                ->where($where)
                ->count();
            $list = $this->model
                ->alias("ua")
                ->join("__USER__ u","ua.master_user_id = u.c_user_id","LEFT")
                ->where($where)
                ->field("u.c_user_id,u.nickname,u.create_time,ua.id,count(ua.id) as apprentice_count,ua.master_user_id")
                ->group("ua.master_user_id")
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $key => &$value) {
                $value['details_url'] = "/admin/user/user/index?c_user_id=".$value['c_user_id'];
                if(isset($where['ua.create_time'])){
                    $value['apprentice_count'] = $this->model->where(['master_user_id'=>$value['master_user_id'],'type'=>1,"status"=>1])->count();
                }
                //查询徒弟
                $value['search_apprentice_count'] = $this->model->where(['master_user_id'=>$value['master_user_id'],'type'=>1,"status"=>1])->where($map)->count();
                //查询徒孙
                $value['s_apprentice_count'] = $this->model->where(['master_user_id'=>$value['master_user_id'],'type'=>2,"status"=>1])->count();
                $value['search_s_apprentice_count'] = $this->model->where(['master_user_id'=>$value['master_user_id'],'type'=>2,"status"=>1])->where($map)->count();
            }
            $result = array(
                "total" => $total,
                "rows" => $list,
            );
            return json($result);
        }
        $this->getapprentice();
        return $this->view->fetch();
    }
    /**
     * 收徒总数查询
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function getapprentice(){
        $this->model =  new UserApprentice();
        $time = input("time","");
        $map=[];
        if(!empty($time)){
            $time = explode("-", $time);
            $s_time = $time[0]."-".$time[1]."-".$time[2];
            $e_time = $time[3]."-".$time[4]."-".$time[5];
            $map['create_time'] = ['between time',[strtotime($s_time),strtotime($e_time)]];
        }
        //收徒总数
        $all_apprentice_count =$this->model->where(['type'=>1,'status'=>1])->where($map)->count();
        // echo $this->model->getLastSql();die;
        $all_apprentice_ren = count($this->model->distinct(true)->where(['type'=>1,'status'=>1])->where($map)->field("master_user_id")->select());
        $all_s_apprentice_count  = $this->model->where(['type'=>2,'status'=>1])->where($map)->count();
        $all_s_apprentice_ren = count($this->model->distinct(true)->where(['type'=>2,'status'=>1])->where($map)->field("master_user_id")->select());
        $return =[
            "all_apprentice_count"=>$all_apprentice_count,
            "all_apprentice_ren"=>$all_apprentice_ren,
            "all_s_apprentice_count"=>$all_s_apprentice_count,
            "all_s_apprentice_ren"=>$all_s_apprentice_ren,
        ];
        if ($this->request->isAjax()){
            return json($return);
        }else{
            $this->assign("data",$return);
        }
    }


}
