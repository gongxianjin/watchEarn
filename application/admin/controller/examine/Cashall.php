<?php

namespace app\admin\controller\examine;

use app\common\controller\Backend;
use think\Controller;
use think\Request;
use think\Db;
//use app\model\Video;
use app\model\UserCashRecord;
use Payment\WxCash;

/**
 * 微信配置管理
 *
 * @icon fa fa-circle-o
 */
class Cashall extends Backend
{

    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new UserCashRecord();
    }

    /**
     * 查看
     */
  public function index()
{
    if ($this->request->isAjax())
    {
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
      
        $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

        $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
        if(!empty($list)){
            if(!empty($list)){
                foreach ($list as $key => &$value) {
                    $value['id'] = strval($value['id']);
                    $value['details_url'] = "/admin/user/user/index?c_user_id=".$value['user_id'];
                }
            }
        }
        $result = array("total" => $total, "rows" => $list);
        return json($result);
    }
    return $this->view->fetch();
}
   
   
    

}
