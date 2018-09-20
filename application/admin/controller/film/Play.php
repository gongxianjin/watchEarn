<?php

namespace app\admin\controller\film;

use app\common\controller\Backend;
use think\Db;

/**
 * 资源管理
 *
 * @icon fa fa-list-alt
 */
class Play extends Backend
{

    protected $model = null;
    protected $searchFields = 'id,r_id,title';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('ResourcesPlay');
    }

     /**
     * 查看
     */
    public function index()
    {
        $ids = input("ids");
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $r_id = input("r_id");
            $map['r_id'] = $r_id;
            $total = $this->model
                    ->where($map)
                    ->order($sort, $order)
                    ->count();
           $sql = $this->model->getLastSql();
            $list = $this->model
                    ->where($map)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            $result = array("total" => $total, "rows" => $list,'sql'=>$sql);
            return json($result);
        }
        $this->assign("ids",$ids);

        return $this->view->fetch();
    }
     /**
     * 添加
     */
    public function add()
    {
        $r_id = input("r_id");
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            

            if ($params)
            {
                foreach ($params as $k => &$v)
                {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }
                try
                {
                    //是否采用模型验证
                    if ($this->modelValidate)
                    {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
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
        $type =  Db('resources')->where(['id'=>$r_id])->value('f_type');
        $this->assign("type",$type);
        return $this->view->fetch();
    }
    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
         $r_id = input("r_id");
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                foreach ($params as $k => &$v)
                {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }

                if (isset($params['mode'] ) && $params['mode'] == 'json')
                {
                    //JSON字段
                    $fieldarr = $valuearr = [];
                    $field = $this->request->post('field/a');
                    $value = $this->request->post('value/a');
                    foreach ($field as $k => $v)
                    {
                        if ($v != '')
                        {
                            $fieldarr[] = $field[$k];
                            $valuearr[] = $value[$k];
                        }
                    }
                    $params['value'] = json_encode(array_combine($fieldarr, $valuearr), JSON_UNESCAPED_UNICODE);
                }
                unset($params['mode']);
                try
                {
                    //是否采用模型验证
                    if ($this->modelValidate)
                    {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    $result = $row->save($params);
                    if ($result !== false)
                    {
                        $this->success();
                    }
                    else
                    {
                        $this->error($row->getError());
                    }
                }
                catch (think\exception\PDOException $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $type =  Db('resources')->where(['id'=>$r_id])->value('f_type');
        $this->assign("type",$type);

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    
 

}
