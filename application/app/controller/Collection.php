<?php
namespace app\app\controller;


use app\model\UserCollection;
use think\Request;
use think\Db;
use app\model\CommentVideo;
use app\model\CommentUpRecords;
use app\model\Video;

class Collection extends BaseController
{
    /**
     * 取消，新增收藏 
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function save()
    {
      

        $type = input("type");//1 取消   2 添加
        $c_type = input("c_type");
        $a_v_id = input("a_v_id");
        $collectionModle = Db::name("user_collection");
        if(empty($type) || !in_array($type, [1,2]) ||  empty($c_type) || !in_array($c_type, [1,2]) || empty($a_v_id)){
            return out([],10001,"错误请求");
        }
        if($type == 1)
        {
            //删除记录
            $collectionModle->where(['c_type'=>$c_type,"c_user_id"=>$this->user_id,"a_v_id"=>$a_v_id])->delete();

            // 添加数量
            Video::update([
                'collect_count'=>[
                    'exp'
                    ,'collect_count-1'
                ]]
                ,[
                    'id'=>['eq',$a_v_id]
                ]
            );
          
            return out([],200,"取消收藏成功");
        }
        if($collectionModle->where(['c_type'=>$c_type,"c_user_id"=>$this->user_id,"a_v_id"=>$a_v_id])->value('id'))
        {
            return out([],200,"收藏成功");
       }

       if(!$collectionModle->insert([
            'c_user_id'=>$this->user_id,
            "c_type"=>$c_type,
            "a_v_id"=>$a_v_id,
            "create_time"=>time()
        ])){
            return out([],10001,"收藏失败");
        }
        // 添加数量
        Video::update([
            'collect_count'=>['exp','collect_count+1']
        ],[
                'id'=>['eq',$a_v_id]
            ]
        );

        return out([],200,"收藏成功");
    }

    /**
     * 收藏列表
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function lists(){
        $c_type = input("c_type");
        if(empty($c_type) || !in_array($c_type, [1,2])){
            return out([],10001,"错误请求");
        }

        $limit =input("limit/d",10);
        $collectionModle =new UserCollection();
        if($c_type == 1){
            $list = $collectionModle
                ->alias('a')
                ->join("__VIDEO__ v","v.id=a.a_v_id")
                ->where(['a.c_user_id'=>$this->user_id,'a.c_type'=>$c_type])
                ->field("v.*,a.create_time,a.id as c_id")
                ->order("a.create_time DESC")
                ->paginate($limit)
                ->toArray();
            foreach ($list['data'] as $key => &$value) {
                $value['video_duration'] = secToTime(substr($value['video_duration'], 0, -2));
                $value['id'] = strval($value['id']);
            }
            $return['list'] = $list['data'];
        }else{
            $return['list'] =[];
        }
        return out($return);
    }
    /**
     * 视频详情
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function videoDetail(){
        $id = input("id");
        if(empty($id)){
             return out([],10001,"错误请求");
        }
        $collectionModle =new UserCollection();
        $data = $collectionModle
                ->alias('a')
                ->join("__VIDEO__ v","v.id=a.a_v_id")
                ->where(['v.id'=>$id,'a.c_user_id'=>$this->user_id])
                ->field("v.*")
                ->find();
        if(empty($data)){
            return out([],10001,"错误请求");
        }

        $videoComments = (new CommentVideo())->geTopCommentsList($data['id']);
        $upMsg = [];
        if(!empty($videoComments)){
            $idList = [0];
            //array_column($videoComments, 'id');
            foreach ($videoComments as $k => $v) {
                $idList[]=$v['id'];
            }
            $CommentUpRecords = new CommentUpRecords();
            $upMsg = $CommentUpRecords->where(['comment_id'=>['in',$idList],'user_id'=>$this->user_id,'type'=>1])->column("user_id","comment_id");
            
        }

        $tempVideoComments = [];
        foreach ($videoComments as &$item){
            $item = $item->toArray();
            if(isset($upMsg[$item['id']])){
             $item['is_up'] = true;//已赞
            }else{
                $item['is_up'] = false;//未赞
            }
            if($item['user_id'] == $this->user_id){
                $item['is_sure'] = true;//是
            }else{
                $item['is_sure'] = false; //不是
            }
            !isset($tempVideoComments[$item['video_id']]) && $tempVideoComments[$item['video_id']] = [];
            $tempVideoComments[$item['video_id']][] = $item;
        }
        $data['top_comments'] = isset($tempVideoComments[$data['id']])?$tempVideoComments[$data['id']]:[];
        return out($data);

    }


}
