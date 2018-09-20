<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/10
 * Time: 16:24
 */

namespace app\admin\controller\retail;

use app\common\controller\Backend;
use app\model\Retail;

class Lists extends Backend
{
    protected $model = null;
    public $multiFields= "status";
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Retail();
    }

    function index()
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

                    //foreach ($list as $key => &$value) {
                    //$value['create_time'] = date('Y-m-d H:i',$value['create_time']);
                    //}
                }
            }

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return view();
    }
}