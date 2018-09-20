<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/18
 * Time: 19:43
 */
namespace app\app\controller;

use app\common\service\DummyService;
use app\common\service\FollowService;
use app\common\service\UserService;
use app\model\NewVideo;

class Work extends BaseController
{
    //作品 列表
    function workList()
    {
        $user_id = $this->user_id;
        $params = $this->params;
        $du_type = isset($params['du_type']) ? $params['du_type'] : 2;
        $other_id = isset($params['other_id']) ? $params['other_id'] : '';
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 20;
        if(empty($other_id)){
            $other_id = $user_id;
            $du_type = 1;
        }

        if($du_type == 1){
            $order = "create_time desc";
            $service = new UserService();
            $ret = $service->getWorkByUser($other_id,$page,$pageSize,$order);
        }else{
            $model = new NewVideo();

            $ret = $model->where(['du_id' => $other_id])->order('order_time desc')->page($page,$pageSize)->select();

            if(empty($ret))
                $ret = [];
            else
                foreach ($ret as $key => $val){
                    $ret[$key]['du_type'] = 2;
                    $ret[$key]['user_id'] = $val['du_id'];
                }
        }

        return out($ret,200,'success');
    }

    //关注的人列表
    function followList()
    {
        $user_id = $this->user_id;
        $params = $this->params;
        $du_type = isset($params['du_type']) ? $params['du_type'] : 2;
        $other_id = isset($params['other_id']) ? $params['other_id'] : '';
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 20;
        if(empty($other_id)){
            $other_id = $user_id;
            $du_type = 1;
        }

        if($du_type == 1){
            $service = new FollowService();
            $list = $service->getUserFollowList($other_id,$page,$pageSize);
        }else{
            $service = new DummyService();
            $list = $service->DummyFollow($other_id,$page,$pageSize);
        }

        return out($list,200,'success');
    }

    //用户粉丝列表
    function fansList()
    {
        $user_id = $this->user_id;
        $params = $this->params;
        $du_type = isset($params['du_type']) ? $params['du_type'] : 2;
        $other_id = isset($params['other_id']) ? $params['other_id'] : '';
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['page_size']) ? $params['page_size'] : 20;
        if(empty($other_id)){
            $other_id = $user_id;
            $du_type = 1;
        }

        $service = new FollowService();
        $list = $service->getFansList($other_id,$du_type,$page,$pageSize);

        return out($list,200,'success');
    }
}