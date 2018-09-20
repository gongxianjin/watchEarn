<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/31
 * Time: 17:34
 */
namespace app\app\controller;

use app\common\service\UserService;
use app\model\DummyFollow;
use app\model\DummyUser;
use app\model\NewVideo;
use app\model\UserLike;

class Abutment extends BaseController
{
    //获取用户信息
    function getOtherInfo()
    {
        $userInfo = $this->userInfo;
        $openId = empty($userInfo['openId']) ? "" : $userInfo['openId'];
        $du_id = input('post.du_id','');
        $otherId = input('post.otherId','');
        if(empty($otherId) && empty($du_id)){
            $otherId = $openId;
        }
        if($otherId){
            $service = new UserService();
            //获取第三方用户信息
            $otherInfo = $service->getOtherUser($openId,$otherId);
            if(!$otherInfo){
                return out([],200,'user error');
            }

            $user = new \app\model\User();
            $userInfo = $user->where(['unique' => $openId])->find();
            $otherInfo['birthday'] = $userInfo->birthday;
            $otherInfo['sex'] = $userInfo->sex;
            $otherInfo['signature'] = empty($userInfo->signature)? "": $userInfo->signature;

            $otherInfo['isFollow'] = false;
            if($openId != $otherId){
                $followInfo = $service->getFollowStatus($openId,$otherInfo);
                if(is_array($followInfo) && $followInfo['both_status']>0){
                    $otherInfo['isFollow'] = true;
                }
            }else{
                $follow = new DummyFollow();
                $followDuNum = $follow->where(['user_id' => $this->user_id,'status' => 1])->count();
                $otherInfo['toAttentionNum'] = $otherInfo['toAttentionNum'] + $followDuNum;
            }
        }else{
            $user = new DummyUser();
            $follow = new DummyFollow();
            $video = new NewVideo();
            $other = $user->find($du_id);
            if(empty($other)){
                return out([],200,'user error');
            }
            $isNewRecord = $follow->where(['user_id' => $this->user_id,'follow_user_id'=> $du_id,'status' => 1])->find();

            //虚假用户生成关注数量
            $likeUserKey = "DummyUserFollow_"  . $du_id;
            $followNum = cache($likeUserKey);
            if(empty($followNum)){
                $followNum = rand(1,20);
                cache($likeUserKey,$followNum);
            }

            //虚假用户生成喜欢视频数量
            $likeVideoKey = "DummyUserLikeVideo_"  . $du_id;
            $likeVideoNum = cache($likeVideoKey);
            if(empty($likeVideoNum)){
                $likeVideoNum = rand(6,50);
                cache($likeVideoKey,$followNum);
            }


            $otherInfo['upNum'] = $video->where(['du_id' => $du_id])->sum('like_count');
            $otherInfo['sex'] = 1;
            $otherInfo['likeVideoNum'] = $likeVideoNum;
            $otherInfo['isFollow'] = empty($isNewRecord) ?  false : true;
            $otherInfo['photo'] = $other['user_avatar'];
            $otherInfo['name'] = $other['nickname'];
            $otherInfo['du_id'] = $other['id'];
            $otherInfo['toAttentionNum'] = $followNum;
            $otherInfo['attentionedNum'] = $follow->where(['follow_user_id'=> $du_id,'status' => 1])->count();
        }

        return out($otherInfo,200,"success");
    }

    //获取我关注的用户列表
    function getMyFollowUser()
    {
        $openId = $this->userInfo['openId'];
        $service = new UserService();
        $list = $service->getFollowList($openId);
        if(empty($list)){
            $list = [];
        }

        $dummy = new DummyFollow();
        $duList = $dummy->where(['user_id' => $this->user_id,'status' => 1])->select();
        if(!empty($duList)){
            foreach ($duList as $val){
                if(!is_object($val->joinFollowUser)){
                    continue;
                }
                $temp = [];
                $temp['du_id'] = $val->joinFollowUser->id;
                $temp['name'] = $val->joinFollowUser->nickname;
                $temp['photo'] = $val->joinFollowUser->user_avatar;
                $list[] = $temp;
            }
        }

        return out($list,200,'success');
    }

    //我点赞的视频
    function getMyLikeList()
    {
        //两边数据合并分页  首先呈现 抓取视频
        $openId = $this->userInfo['openId'];
        $page = input('post.page',1);
        $pagesize = input('post.pagesize',20);
        $pagesize = $pagesize < 10 ? 20 : $pagesize;

        $model = new UserLike();
        $service = new UserService();
        $total = $model->where(['c_user_id' => $this->user_id,'c_type' => 2])->count();
        $maxPage = intval($total / $pagesize);
        if($total % $pagesize != 0){
            $maxPage++;
        }

        //情况1 抓取视频可以满足前端页面数据填充要求
        if($maxPage > $page || ($total % $pagesize == 0 && $maxPage == $page)){
            $list = $model->where(['c_user_id' => $this->user_id,'c_type' => 2])->order('create_time desc')->page($page,$pagesize)->select();
            $retData = [];
            foreach ($list as $val){
                if(!is_object($val->joinVideo)){
                    continue;
                }
                $temp['id'] = $val->joinVideo->id;
                $temp['video_url'] = $val->joinVideo->video_url;
                $temp['videothumbnail'] = $val->joinVideo->video_cover;
                $temp['thumbnumber'] = $val->joinVideo->like_count;
                $temp['commentnumber'] = $val->joinVideo->comment_count;
                $temp['transmitnumber'] = $val->joinVideo->share_count;
                $temp['videotitle'] = $val->joinVideo->title;
                $temp['isthumb'] = 1;
                $temp['aliyunvideoid'] = "";
                $temp['username'] = $val->joinVideo->joinDummy->nickname;
                $temp['du_id'] =  $val->joinVideo->du_id;
                $temp['photo'] = $val->joinVideo->joinDummy->user_avatar;
                $temp['isopen'] = 1;

                $retData[] = $temp;
            }
            return out($retData,200,'success');
        }

        //情况二  前端需求 要两边数据整合
        if($maxPage == $page){
            $list = $model->where(['c_user_id' => $this->user_id,'c_type' => 2])->order('create_time desc')->page($page,$pagesize)->select();
            $retData = [];
            foreach ($list as $val){
                if(!is_object($val->joinVideo)){
                    continue;
                }
                $temp['id'] = $val->joinVideo->id;
                $temp['video_url'] = $val->joinVideo->video_url;
                $temp['videothumbnail'] = $val->joinVideo->video_cover;
                $temp['thumbnumber'] = $val->joinVideo->like_count;
                $temp['commentnumber'] = $val->joinVideo->comment_count;
                $temp['transmitnumber'] = $val->joinVideo->share_count;
                $temp['videotitle'] = $val->joinVideo->title;
                $temp['isthumb'] = 1;
                $temp['aliyunvideoid'] = "";
                $temp['username'] = $val->joinVideo->joinDummy->nickname;
                $temp['du_id'] =  $val->joinVideo->du_id;
                $temp['photo'] = $val->joinVideo->joinDummy->user_avatar;
                $temp['isopen'] = 1;

                $retData[] = $temp;
            }

            $otherNum = $pagesize - $total%$pagesize;
            $res = $service->getMyLikeVideoList($openId,0,$otherNum);

            if(empty($res)){
                return out($retData,200,'success');
            }
            foreach ($res as $key => $val){
                $res[$key]['otherId'] = $val['userid'];
            }

            //合并数组
            $retData = array_merge($retData,$res);
            return out($retData,200,'success');
        }

        //情况三 前端需求 抓取视频不满足
        if($page > $maxPage){
            $otherNum = 0;
            if($total%$pagesize != 0){
                $otherNum = $pagesize - $total%$pagesize;
            }

            $start = $otherNum + ($page - $maxPage - 1) * $pagesize;
            $limit = $pagesize;

            $res = $service->getMyLikeVideoList($openId,$start,$limit);

            if(!$res){
                return out([],200,'success');
            }
            foreach ($res as $key => $val){
                $res[$key]['otherId'] = $val['userid'];
            }

            return out($res,200,'success');
        }

    }

    //关注虚假用户
    function followUser()
    {
        $duId = input('post.du_id','');
        if(empty($duId)){
            return out([],200,"user error");
        }
        $user_id = $this->user_id;
        $m = new DummyFollow();
        $duUser = new DummyUser();
        //虚假用户是否存在
        $userExcite = $duUser->find($duId);
        if($userExcite === false){
            return out([],200,"connect error");
        }
        if(empty($userExcite)){
            return out([],200,"nothing user");
        }

        //查询关注信息
        $follow = $m->where(['user_id' => $user_id,'follow_user_id' => $duId])->find();
        if($follow === false){
            return out([],200,"connect error");
        }

        $is_follow = 1;
        //关注信息
        if(empty($follow)){
            //添加默认关注
            $data['user_id'] = $user_id;
            $data['follow_user_id'] = $duId;
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['status'] = 1;
            $re = $m->insert($data);
        }else{
            //如果是关注 则取消 反之亦然
            $follow->update_time = time();
            if($follow->status != 1){
                $follow->status = 1;
            }else{
                $follow->status = 2;
                $is_follow = 0;
            }
            $re = $follow->save();
        }

        //是否修改成功
        if($re){
            return out(['is_follow' => $is_follow],200,"success");
        }

        return out([],10003,"error");
    }

    //获取虚拟用户作品
    function getDuVideoList()
    {
        $du_id = input('post.du_id',0);
        $page = input('post.page',1);
        $pagesize = input('post.pagesize',10);

        $videModel = new NewVideo();
        $likeModel = new UserLike();
        $duModel = new DummyUser();
        $dummyUser = $duModel->where(['id' => $du_id])->find();
        if(empty($dummyUser)){
            return out([],200,'user message error!');
        }

        $list = $videModel->where(['du_id' => $du_id])->order("create_time desc")->page($page,$pagesize)->select();
        if(empty($list)){
            return out([],200,'success');
        }
        $retData = [];
        foreach ($list as $val){
            $temp = [];
            $likeExite = $likeModel->where(['c_user_id' => $this->user_id,'a_v_id' => $val->id,'c_type' => 2])->find();
            if(empty($likeExite)){
                $isLike = 0;
            }else{
                $isLike = 1;
            }

            $temp['id'] = $val->id;
            $temp['video_url'] = $val->video_url;
            $temp['videothumbnail'] = $val->video_cover;
            $temp['thumbnumber'] = $val->like_count;
            $temp['commentnumber'] = $val->comment_count;
            $temp['transmitnumber'] = $val->share_count;
            $temp['videotitle'] = $val->title;
            $temp['isthumb'] = $isLike;
            $temp['aliyunvideoid'] = "";
            $temp['username'] = $dummyUser['nickname'];
            $temp['du_id'] = $du_id;
            $temp['photo'] = $dummyUser['user_avatar'];
            $temp['isopen'] = 1;

            $retData[] = $temp;
        }
        return out($retData,200,'success');
    }

    //获取用户粉丝列表
    function getDuFansUser()
    {
        $du_id = input('post.du_id',0);
        $page = input('post.page',1);
        $pagesize = input('post.pagesize',10);

        $followModel = new DummyFollow();
        $list = $followModel->where(['follow_user_id' => $du_id,'status' => 1])->order('create_time asc')->page($page,$pagesize)->select();

        if(empty($list)){
            return out([],200,'success');
        }
        $retData = [];
        foreach ($list as $val){
            if(!is_object($val->joinUser)){
                continue;
            }
            $temp = [];
            $temp['id'] = $val->joinUser->unique;
            $temp['name'] = $val->joinUser->nickname;
            $temp['photo'] = $val->joinUser->headimg;
            $temp['sex'] = $val->joinUser->sex;

            $retData[] = $temp;
        }
        return out($retData,200,'success');
    }

    //获取用户关注信息
    function getDuFollowUser()
    {
        $du_id = input('post.du_id',0);
        $page = input('post.page',1);
        $pageSize = input('post.pagesize',10);
        if($page < 1){
            $page = 1;
        }
        if(empty($du_id)){
            return out([],200,'error');
        }
        //虚假用户生成关注数量
        $likeUserKey = "DummyUserFollow_"  . $du_id;
        $followNum = cache($likeUserKey);
        if(empty($followNum)){
            $followNum = rand(1,20);
            cache($likeUserKey,$followNum);
        }

        //虚假用户生成关注数量
        $likeUserListKey = "DummyUserFollowList_"  . $du_id;

        $list = cache($likeUserListKey);
        if(empty($list)){
            $userModel = new \app\model\User();
            $userList = $userModel->where(['status' => 1,'is_cross_read_level' => 0,'headimg' => ['neq','']])->order('rand()')->limit($followNum)->select();
            $list = [];
            foreach ($userList as $val){
                $temp = [];
                $temp['id'] = $val->unique;
                $temp['name'] = $val->nickname;
                $temp['photo'] = $val->headimg;
                $temp['sex'] = $val->sex;
                $temp['otherId'] = $val->unique;
                $list[] = $temp;
            }

            cache($likeUserListKey,$list);
        }
        $startKey = ($page - 1) * $pageSize;
        $tempNum = $pageSize;
        $retList =  [];
        foreach ($list as $key => $item)
        {
            if($tempNum <=0){
                break;
            }
            if($key >= $startKey){
                $retList[] = $item;
                $tempNum--;
            }
        }

        return out($retList,200,'success');
    }

    //获取虚拟用户喜欢视频
    function getDuLikeVideoList()
    {
        $du_id = input('post.du_id',0);
        $page = input('post.page',1);
        $pageSize = input('post.pagesize',10);
        if($page < 1){
            $page = 1;
        }

        //虚假用户生成关注数量
        $likeVideoKey = "DummyUserLikeVideo_"  . $du_id;

        $likeVideoNum = cache($likeVideoKey);
        if(empty($likeVideoNum)){
            $likeVideoNum = rand(6,50);
            cache($likeVideoKey,$likeVideoNum);
        }

        //虚假用户生成喜欢视频
        $likeVideoListKey = "DummyUserLikeVideoList_"  . $du_id;

        $list = cache($likeVideoListKey);
        if(empty($list)){
            $video = new NewVideo();
            $lists = $video->where(['like_count' => ['>',10000]])->order('rand()')->limit($likeVideoNum)->select();
            $list = [];
            foreach ($lists as $val){
                $temp = [];
                $temp['id'] = $val->id;
                $temp['video_url'] = $val->video_url;
                $temp['videothumbnail'] = $val->video_cover;
                $temp['thumbnumber'] = $val->like_count;
                $temp['commentnumber'] = $val->comment_count;
                $temp['transmitnumber'] = $val->share_count;
                $temp['videotitle'] = $val->title;

                $list[] = $temp;
            }

            cache($likeVideoListKey,$list);
        }

        $startKey = ($page - 1) * $pageSize;
        $tempNum = $pageSize;
        $retList =  [];
        foreach ($list as $key => $item)
        {
            if($tempNum <=0){
                break;
            }
            if($key >= $startKey){
                $retList[] = $item;
                $tempNum--;
            }
        }

        return out($retList,200,'success');
    }
}