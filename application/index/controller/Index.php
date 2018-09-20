<?php
namespace app\index\controller;

use app\common\model\Redis;
use app\common\MyController;
use app\model\TempUser;
use app\model\User;
use app\model\VideoComment;
use think\Request;

class Index extends MyController
{
    public function index(Request $request)
    {

        for($i=1;$i<=1000;$i++){
            $m = VideoComment::instance($i);
            $data = [];
            $data['video_id'] = $i;
            $data['parent_id'] = 0;
            $data['create_time'] = time();
            $data['content'] = '测试评论 视频ID ： '.$i;
            $data['user_id'] = 123;
            $m->insert($data);
        }

        return 'success';

    }
}