<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/17
 * Time: 16:22
 */

namespace app\admin\controller\aliyun;

use app\common\controller\Backend;
use app\common\logic\UserVideoLogic;
use app\common\service\DemandService;
use app\model\UserVideo;
use think\Exception;

class Video extends Backend
{
    protected $model = null;
    protected $relationSearch = true;
    public $multiFields= "status";
    public function _initialize()
    {
        parent::_initialize();

        $this->model = new UserVideo();
    }
    function index()
    {
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with('joinUser')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with('joinUser')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            if(!empty($list)){
                foreach ($list as $key => $val){
                    $list[$key]['w_h'] = $val['video_width'] . '/' . $val['video_height'];
                    $list[$key]['User'] = $val->joinUser;
                }
            }

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return view();
    }

    //播放
    function play($ids=null){
        $logic = new UserVideoLogic();

        $info = $logic->findByCondition(['id' => $ids]);
        if(empty($info)){
            $this->error();
        }
        $aliyunId = $info['aliyun_video_id'];

        $service = new DemandService();
        $res = $service->getPlayInfo($aliyunId);
        try{
            $res = json_decode(json_encode($res),true);
        }catch (Exception $e){
            $this->error();
        }

        $this->assign('cover',$res['VideoMeta']['CoverURL']);
        $this->assign('auth',$res['PlayAuth']);
        $this->assign('aliyunVideoId',$aliyunId);

        return view();
    }

}