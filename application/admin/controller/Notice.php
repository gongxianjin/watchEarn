<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use \app\model\Notice as NoticeM;
use think\Request;

/**
 * 后台首页
 * @internal
 */
class Notice extends Backend
{

    private static $IS_PUSH_STATUS =[
        0=>"准备推送",
        1=>"已推送",
        2=>"未推送",
    ];

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new NoticeM();
    }

    /**
     * 后台首页
     */
    public function index()
    {
       echo $this->fetch();
    }

    public function all()
    {
        $result = ["total" => $this->model->count(), "rows" => $this->model->select()];



        foreach ($result['rows'] as &$item)
        {

            $item['type'] = $item['type']==1?'系统通知':'App弹屏';
            $item['push_status'] = isset( self::$IS_PUSH_STATUS[$item['is_push']] ) ? self::$IS_PUSH_STATUS[$item['is_push']] : "未知状态:{$item['is_push']}";

        }
        return json($result);
    }

    public function add()
    {
        if ($this->request->isPost()){
            $params = Request::instance()->param("row/a");
            $params['start_date'] = strtotime($params['start_date']);
            $params['end_date'] = strtotime($params['end_date']);
            $this->model->save($params);
            $this->success();
        }else
            return $this->view->fetch();

    }


    public function edit($ids = NULL)
    {
        if ($this->request->isPost()){
            $params = Request::instance()->param("row/a");
            $params['start_date'] = strtotime($params['start_date']);
            $params['end_date'] = strtotime($params['end_date']);
            $this->model->update($params,['id'=>$ids]);
            $this->success();
        }else{
            $ids = input('ids');

            $obj = $this->model->get($ids);
            $obj['start_date'] = date('Y-m-d H:i:s',$obj['start_date']);
            $obj['end_date'] = date('Y-m-d H:i:s',$obj['end_date']);
            $this->view->assign('obj',$obj);
            return $this->view->fetch();
        }

    }


}
