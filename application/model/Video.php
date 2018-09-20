<?php
namespace app\model;

use think\Model;
use think\Db;
use think\Request;
use \app\common\model\Redis;

class Video extends Model
{

    protected $updateTime = false;

    /**
     * 随机获取
     *
     * @param int $count

     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function rand($count=20)
    {
        $videoCount = self::count();
        $offset = $videoCount-$count;
        $offset = rand(0,$offset);
        $result = self::limit($offset,$count)->select();
        return $result;
    }


    /**
     * @param int $page
     * @param int $count
     * @param string $keyWords
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function searchPages($page = 1,$count=20,$keyWords = '')
    {
        //$query = self::page($page,$count)->order('order_time','DESC');
        $map['status']=1;
        $map['title|user_nickname']= ['like',"%$keyWords%"];
       // $query->where($map);
        $list = self::where($map)->order('order_time','DESC')->paginate($count)->toArray();
        return $list['data'];
    }
    /**
     * 列表数据
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
     public static function pages($page = 1,$count=20,$userInfo)
    {
        $limit = $count;
        $redis_table = ['key'=>'user','field'=>'page'];
        $redis_token = "userVideoInfo";
        $redis_key = $userInfo['c_user_id'];//用户id
        $redis_timeout = 432000;//储存时间
        $redis = Redis::instance();
        $Info = $redis->hget_json($redis_table,$redis_token,$redis_key);
        $page = 1;
        $order_time ="";
        if(!empty($Info)){
            $page = $Info['page']+1;
            $order_time = $Info['s_time'];
        }
        $map =[
            'status'=>1,//正常数据
        ];
        $normalPage = true;//是否正常分页
        if(!empty($order_time)){
            $newCount = self::where($map)->where(['order_time'=>['>',$order_time]])->count("id");
            if($newCount>=$limit){
                    Request::instance()->post(['page'=>1]);
                    Request::instance()->get(['page'=>1]);
                $list = self::where($map)->where(['order_time'=>['>',$order_time]])->order("order_time ASC,id ASC")->paginate($limit)->toArray();
                if(!empty($list)){
                    $normalPage = false;//非正常分页
                }
            } 
        }
        if($normalPage){
            Request::instance()->post(['page'=>$page]);
            Request::instance()->get(['page'=>$page]);
            $list = self::where($map)->order("order_time DESC")->paginate($limit)->toArray(); 
        }
        if(count($list['data']) < $limit){
            $page = 0;
        }
        if(!empty($list)){
            if($page == 1 || !$normalPage){
                //查询开始的最大排序时间
                 $Info = ['user_id'=>$redis_key,"page"=>$page,"s_time"=>$list['data']['0']['order_time']];
            }else{
                if(empty($Info['s_time'])){
                    $s_time = time();
                }else{
                    $s_time = $Info['s_time'];
                }
                $Info = ['user_id'=>$redis_key,"page"=>$page,"s_time"=>$Info['s_time']];
            }
            $result = $list['data'];
        }else{
            $Info = ['user_id'=>$redis_key,"page"=>1,"s_time"=>""];
            $result = [];
        }
        $redis->hset_json($redis_table,$redis_token,$redis_key,$Info,$redis_timeout);
        return $result;
    }
    /**
     * 搜索查询
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public static function search($key,$count=20){
        $map['status'] = 1;
        $map['title|user_nickname'] = ['like',"%$key%"];
        return self::where($map)->limit($count)->select();
    }



}
