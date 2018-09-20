<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/17
 * Time: 16:22
 */

namespace app\admin\controller\aliyun;

use app\common\controller\Backend;
use app\model\Tag;

class Tags extends Backend
{
    protected $model = null;
    protected $relationSearch = true;
    public $multiFields= "status";
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Tag();
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

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return view();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");

            if ($params)
            {
                foreach ($params as $k => &$v)
                {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }

                //重组tag_name 字段
                if ($params['mode'] == 'json') {
                    //JSON字段
                    $fieldarr = $valuearr = [];
                    $rowfield = $rowvalue = [];
                    $field = $value = [];

                    // key field
                    $field = $this->request->post('field/a');

                    $value = $this->request->post('value/a');

                    //key row[field]
                    if (isset($params['field'])) {
                        $rowfield = explode(',', $params['field']);
                        $rowvalue = explode(',', $params['value']);
                    }

                    if (isset($field)) {
                        $field = array_merge($field, $rowfield);
                        $value = array_merge($value, $rowvalue);
                    } else {
                        $field = $rowfield;
                        $value = $rowvalue;
                    }

                    foreach ($field as $k => $v) {
                        if ($v != '') {
                            $fieldarr[] = $field[$k];
                            $valuearr[] = $value[$k];
                        }
                    }
                    $params['tag_name'] = json_encode(array_combine($fieldarr, $valuearr), JSON_UNESCAPED_UNICODE);
                }

                unset($params['mode']);
                unset($params['field']);
                unset($params['value']);


                try
                {
                    $result = $this->model->save($params);

                    if ($result !== false)
                    {
                        $this->success();
                    }
                    else
                    {
                        $this->error($this->model->getError());
                    }
                }
                catch (\think\exception\PDOException $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

}