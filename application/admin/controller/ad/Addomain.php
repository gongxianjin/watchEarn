<?php

namespace app\admin\controller\ad;

use app\common\controller\Backend;
use think\Controller;
use think\Request;
use app\model\AdDomain as AdDomainModel ;

/**
 * 微信配置管理
 *
 * @icon fa fa-circle-o
 */
class Addomain extends Backend
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new AdDomainModel();
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
                   
                    $value['type'] = str_replace([1,2,3], ['跳转域名','显示域名','第三方'], $value['type']);
                    $value['status'] = str_replace([1,2], ['正常','已封'], $value['status']);
                    $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
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
                    $pre = "/^((http|https):\/\/)/";
                    if(preg_match($pre,$params['domain_name']) == 0){
                        $params['domain_name'] = "http://".$params['domain_name'];
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
                    $pre = "/^((http|https):\/\/)/";
                    if(preg_match($pre,$params['domain_name']) == 0){
                        $params['domain_name'] = "http://".$params['domain_name'];
                    }
                    if($params['status'] == 2){
                        $params['letter_time'] = time();
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
        

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
  

}
