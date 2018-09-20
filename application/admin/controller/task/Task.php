<?php

namespace app\admin\controller\task;

use app\common\controller\Backend;
use think\Controller;
use think\Request;

/**
 * 微信配置管理
 *
 * @icon fa fa-circle-o
 */
class Task extends Backend
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('gold_run');
    }
    /**
     * 页面数据
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function index(){
         //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name'))
            {
                return $this->selectpage();
            }
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
                foreach ($list as $key => &$value) {
                    $value['tile_type'] = str_replace([1,2,3,4], ['日常任务','新手任务','特殊任务','其它任务'], $value['tile_type']);
                    $value['is_activation'] = str_replace([0,1], ['无效','有效'], $value['is_activation']);
                    $value['button_type'] = str_replace([1,2], ['url','app'], $value['button_type']);
                    $value['effective'] = ($value['expire_time'] ==9999  )?"永久有效":date('Y-m-d H',$value['activation_time'])."至".date('Y-m-d H',$value['expire_time']);
                }
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
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
                try
                {
                    //是否采用模型验证
                    if ($this->modelValidate)
                    {
                        //echo 11;die;
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    if(!empty($params['activation_time'])){
                        $params['activation_time'] = strtotime($params['activation_time']);
                    }
                    if(!empty($params['expire_time'])){
                        $params['expire_time'] = strtotime($params['expire_time']);
                    }
                  //  p($params);die;
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

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        //p($row);die;
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
                    if(!empty($params['activation_time'])){
                        $params['activation_time'] = strtotime($params['activation_time']);
                    }
                    if(!empty($params['expire_time'])){
                        $params['expire_time'] = strtotime($params['expire_time']);
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
                catch (\think\exception\PDOException $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        if($row['activation_time']<1){
            $row['activation_time'] = strtotime("2018-01-01");
        }
        if($row['expire_time']<1){
            $row['expire_time'] = strtotime("2030-01-01");
        }
        $d = config("active_type");
       
        $this->view->assign("d", $d);

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    /**
     * 获取播放平台
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function selectpage(){
        $where = "";
        $total = 4;
        $active_type = config("active_type");
        
        foreach ($active_type as $key => $value) {
            $a['id'] = $key;      
            $a['name'] = $value;    
            $list[]=$a;  
        }
        //var_dump($list);die;
        return json(['list' => $list, 'total' => $total]);
    }

}
