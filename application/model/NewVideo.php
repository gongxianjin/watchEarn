<?php
namespace app\model;

use think\Model;
use think\Db;
use think\Request;
use \app\common\model\Redis;

class NewVideo extends Model
{

    protected $updateTime = false;

    function joinDummy()
    {
        return $this->hasOne('DummyUser','id','du_id');
    }

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
     public static function pages($rtype = 1,$page = 1,$count=20,$userInfo)
    {
        $checkKey = "redisKeys_".$rtype;
        $cacheInfo = cache($checkKey);
        if(!empty($cacheInfo)){
            return json_decode($cacheInfo,true);
        }

        $limit = $count;
        $redis_table = ['key'=>'user','field'=>'page'];
        $redis_token = "userVideoInfo".":r_type:".$rtype;

        $redis_key = 99;
        $redis_timeout = 432000;//储存时间
        $redis = Redis::instance();
        $Info = $redis->hget_json($redis_table,$redis_token,$redis_key);
        $page = 1;
        $order_time ="";
        $minLike = 350;


        if(!empty($Info)){
            $page = $Info['page']+1;
            $order_time = $Info['s_time'];
        }
        $map =[
            'status'=>1,//正常数据
            'r_type'=>$rtype, // 类型
            'like_count' => ['egt',$minLike],
            'du_id' => ['gt',0]
        ];
        $normalPage = true;//是否正常分页
        if(!empty($order_time)){
            $newCount = self::where($map)->field('')->where(['order_time'=>['>',$order_time]])->count();
            if($newCount>=$limit) {
                Request::instance()->post(['page'=>$page]);
                Request::instance()->get(['page'=>$page]);
                $list = self::where($map)->where(['order_time'=>['>',$order_time]])->order("order_time ASC,id ASC")->paginate($limit);
                if (!empty($list) && !empty($list['data'])) {
                    $normalPage = false;//非正常分页
                }
            }
        }
        if($normalPage){
            Request::instance()->post(['page'=>$page]);
            Request::instance()->get(['page'=>$page]);
            $list = self::where($map)->order("order_time DESC")->paginate($limit);
        }

        $listData = [];
        foreach ($list as $val){
            $temp = [];
            $temp['id'] = $val->id;
            $temp['title'] = $val->title;
            $temp['video_url'] = $val->video_url;
            $temp['video_duration'] = $val->video_duration;
            $temp['video_cover'] = $val->video_cover;
            $temp['video_height'] = $val->video_height;
            $temp['video_width'] = $val->video_width;
            $temp['like_count'] = $val->like_count;
            $temp['comment_count'] = $val->comment_count;
            $temp['order_time'] = $val->order_time;
            $temp['share_count'] = $val->share_count;
            $temp['play_count'] = $val->play_count;
            $temp['user_nickname'] = $val->joinDummy->nickname;
            $temp['user_avatar'] = $val->joinDummy->user_avatar;
            $temp['user_id'] = $val->du_id;
            $temp['du_type'] = 2;
            $temp['r_type'] = $val->r_type;

            $listData[] = $temp;
        }
        $list = $list->toArray();
        $list['data'] = $listData;

        if(count($list['data']) < $limit){
            $page = 0;
        }
        if(!empty($list) && !empty($list['data'])){
            if($page == 1 || !$normalPage){
                //查询开始的最大排序时间
                 $Info = ['user_id'=>$redis_key,"page"=>$page,"s_time"=>$list['data'][count($list['data'])-1]['order_time']];
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

        //缓存
        cache($checkKey,json_encode($result),['expire' => 3]);
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
