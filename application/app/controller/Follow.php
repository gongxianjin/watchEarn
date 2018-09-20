<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/18
 * Time: 9:29
 */

namespace app\app\controller;
use think\Request;
use think\Validate;
use app\common\service\FollowService;
use app\common\service\UserService;

class Follow extends BaseController {


    /**
     * 关注用户
     * $type_id 真实关注还是虚拟关注
     * $user_id 用户ID
     * $follow_user_id 被关注者ID
     * $video_id 通过某条视频进行的关注
     */
    public function followUser(Request $request){

        $params = $request->param();
        $params['user_id'] = $this->user_id;
        $params['type_id'] = $params['du_type'];
        $params['user_id'] = 2353;
        $validate = new Validate([
            'type_id'=>'require|number',
            'user_id'=>'require|number',
            'follower_id'=>'require|number'
            ],
            [
                'type_id.require'=>' required parameter missing',//缺少必要参数
                'user_id.require'=>'Lack of user information',//缺少用户信息
                'follower_id.require'=>'Lack of followers information',//缺少被关注者信息
            ]);

        if($params['user_id'] == $params['follower_id']){

            return out([],10001,'You can not follow yourself');
        }

        if(!$validate->check($params)){
            return out([],10001,$validate->getError());
        }else{

            $model = new FollowService();
            $userModel = new UserService();

            //查找用户信息
            $user_info = $model->getUserInfo($params['type_id'],$params['user_id']);

            if($user_info['code'] !== 200){
                return out([],10002,$user_info['msg']);
            }

            //被关注者用户信息
            $follower_info = $model->getFollowerInfo($params['follower_id'],$params['type_id']);

            if($follower_info['code'] !== 200){
                return out([],10002,$follower_info['msg']);
            }


            $follow_info = $model->getFollowInfo($params);

            if($follow_info){//已存在  修改关注状态

                $res = $model->editFollowInfo($follow_info);


                if($follow_info['status'] == 1){
                    $user_follow_num['follow_num'] = ['exp',"follow_num-1"];
                    $user_fans_num['fans_num'] = ['exp',"fans_num-1"];
                }else if($follow_info['status'] == 2){
                    $user_follow_num['follow_num'] = ['exp',"follow_num+1"];
                    $user_fans_num['fans_num'] = ['exp',"fans_num+1"];
                }

                $userModel->editUserInfo(['c_user_id'=>$params['user_id']],$user_follow_num,$params['type_id']);

                if($params['type_id'] == 1){//真实用户才会涨粉 脱粉

                    $userModel->editUserInfo(['c_user_id'=>$follower_info['data']['c_user_id']],$user_fans_num,$params['type_id']);
                }


            }else{//新增关注数据

                $res = $model->addFollowInfo($params);


                //关注数
                $userModel->editUserInfo(['c_user_id'=>$params['user_id']],['follow_num'=>['exp',"follow_num+1"]],$params['type_id']);

                if($params['type_id'] == 1){//真实用户才会涨粉

                    $userModel->editUserInfo(['c_user_id'=>$follower_info['data']['c_user_id']],['fans_num'=>['exp',"fans_num+1"]],$params['type_id']);
                }
            }

            if($res !== false){

                return out([],200,'Successful operation');
            }else{
                return out([],10009,'Operation failure');
            }

        }


    }

    //我的关注列表
    function followVideo()
    {
        $user_id = $this->user_id;
        $params = $this->params;
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 20;

        $followService = new FollowService();
        $list = $followService->getFollowVideo($user_id,$page,$pageSize);
        return out($list,200,'success');
    }

}