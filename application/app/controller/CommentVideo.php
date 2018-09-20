<?php
namespace app\app\controller;


use app\model\CommentVideo as CommentVideoModel;
use app\model\CommentUpRecords;
use app\model\Video;
use think\Db;
use think\Request;
use app\app\controller\mission_new\HighQualityComments;

class CommentVideo extends BaseController
{

    public function lists()
    {
        $videoId= input("video_id/s","");

        if(empty($videoId)){
            return out([],10001,"错误请求");
        }
        $order  = input("order/s","time");
        if(!in_array($order, ['up','time'])){
            return out([],10001,"错误请求");
        }
        if($order=="time"){
            $order = "pub_datetime";
        }else if ($order == "up"){
            $order = "like_count";
        }else{
            $order = "id";
        }
        $count = 20;
        $page = input("page/d",1);
        $offset = ($page-1)*$count;
        $data = (new CommentVideoModel())
            ->where('video_id','eq',$videoId)
            ->limit($offset,$count)
            ->order("$order DESC")
            ->select();
        if ($data===null)
            return out([]);
        else
        {
            //查询是否点赞
            $idList = [0];
            foreach ($data as $key => $value) {
                $idList[] = $value['id'];
            }
            $CommentUpRecords = new CommentUpRecords();
            $upMsg = [];
            if(!empty($idList)){
                 $upMsg = $CommentUpRecords->where(['comment_id'=>['in',$idList],'user_id'=>$this->user_id,'type'=>1])->column("user_id","comment_id");
            }
            foreach ($data as &$item)
            {
                $item =$item->toArray();
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
            }
            return out($data);
        }

    }

    public function push()
    {
        $userInfo = &$this->userInfo;

        $request = Request::instance()->param();

        //评论
        $content = '';
        if (isset($request['content']))
            $content = trim($request['content']);
        if ($content===''||mb_strlen($content)>=256)
            return out([],200,'评论错误');

        //视频ID
        $video_id = 0;
        if (isset($request['video_id']))
            $video_id = intval($request['video_id']);
        if ($video_id===0)
            return out([],10002,'视频ID错误');

        Video::update(['comment_count'=>['exp','comment_count+1']],['id'=>$video_id]);

        $comment = new CommentVideoModel();
        $result = $comment->save([
            'content'=>$content
            ,'user_id'=>$userInfo['c_user_id']
            ,'avatar'=>$userInfo['headimg']
            ,'nickname'=>$userInfo['nickname']
            ,'video_id'=>$video_id
        ]);

        if ($result===1)
            return out([],200,'评论成功');
        else
            return out([],10002,'出错了，请重试');

    }

    public function like()
    {
        //访问封号
        $upData = [
            "is_cross_read_level" => 1,
            "gold_flag" => 0,
            "total_gold_flag" =>0,
            "frozen_balance" =>0,
            "balance"=>0,
            "total_balance"=>0,
        ];
        Db::table('user')->where(['c_user_id' => $this->user_id])->update($upData);
        return out(['gold_flag'=>0,'count'=>0],'10002','Suspicious activiity detected, your account has been blocked!');

        if($this->login_flag){
            if($this->userInfo['is_cross_read_level']){
                return out(['gold_flag'=>0,'count'=>0],'10002','Suspicious activiity detected, your account has been blocked!');
            }
        }

        //不间断访问多余10个任务 封号
        $cacheKey = "TaskUserCache__" . $this->user_id;
        $cacheNum = cache($cacheKey);
        if(empty($cacheNum)){
            cache($cacheKey,1,['expire' => 1800]);
        }else{
            if($cacheNum > 5){
                $this->userModel :: getUserInfoById($this->user_id);
            }
            cache($cacheKey,$cacheNum++,['expire' => 1800]);
            if($cacheNum > 10){
                $upData = [
                    "is_cross_read_level" => 1,
                    "gold_flag" => 0,
                    "total_gold_flag" =>0,
                    "frozen_balance" =>0,
                    "balance"=>0,
                    "total_balance"=>0,
                ];
                Db::table('user')->where(['c_user_id' => $this->user_id])->update($upData);
                return out(['gold_flag'=>0,'count'=>0],'10002','Suspicious activiity detected, your account has been blocked!');
            }
        }

        $commentId = input("comment_id",0);
        if (empty($commentId)){
            return out([],10002,'评论ID错误');
        }
        //是否点赞过
        $CommentUpRecords = new CommentUpRecords();
        $is_exists = $CommentUpRecords->where(['user_id'=>$this->user_id,"comment_id"=>$commentId,"type"=>1])->find();
        if($is_exists){
            return out([],10001,"不能重复点赞哦");
        }
        $like_count = 0;
        try{
            $data = CommentVideoModel::find($commentId);
            if($data['user_id'] == $this->user_id){
                 throw new \Exception("不能自己为自己点赞哦", 1);
            }
            if(empty($data)){
                throw new \Exception("错误评论信息", 1);
            }
            $up['like_count'] = $data['like_count']+1;
              //触发优质评论奖励
            $data['like_count'] = $up['like_count'];
            $result = (new HighQualityComments())->run($data);
            if($result){
                $up['is_add_gold'] = 2;
            }
            if(!CommentVideoModel::update($up,['id'=>$commentId])){
                throw new \Exception("点赞失败", 1);
            }
            //添加到攒点记录
            $CommentUpRecords->insert(['user_id'=>$this->user_id,"comment_id"=>$commentId,"type"=>1,"create_time"=>time()]);
        } catch (\Exception $e){
             //throw $e;
            return out([],10001,$e->getMessage());
        }
        $return['count'] = $up['like_count'];
        return out($return,200,'点赞成功');
    }

    public function likeList()
    {
        $params = Request::instance()->param();

        $userInfo = &$this->userInfo;

        $count = 20;

        $page = isset($params['page']) && intval($params['page'])>1?intval($params['page']):1;

        $offset = ($page-1)*$count;

        $data = CommentVideoModel
            ::where('user_id','eq',$userInfo['c_user_id'])
            ->where('like_count','neq',0)
            ->limit($offset,$count)->select();

        if ($data===null)
            return out([]);
        else
        {
            foreach ($data as &$item)
            {
                $item =$item->toArray();
            }
            return out($data);
        }

    }

}
