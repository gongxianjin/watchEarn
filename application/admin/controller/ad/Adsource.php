<?php

namespace app\admin\controller\ad;

use app\common\controller\Backend;
use think\Controller;
use think\Request;
use think\Db;
use app\model\AdSource as  AdSourceModel ;

/**
 * 微信配置管理
 *
 * @icon fa fa-circle-o
 */
class Adsource extends Backend
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new AdSourceModel();
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
                  /*  $value['title'] = implode(json_decode($value['title'],true),PHP_EOL);
                    $imgarr = json_decode($value['img']);
                    $img = "";
                    foreach ($imgarr as $k => $v) {
                       $img.="<a href=".$v." target='_blank'><img src=".$v." alt='' style='max-height:50px;max-width:60px'></a>";
                    }
                    $value['img'] = $img;*/
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
                    if(!ckeckHttp($params['ad_url'])){
                         $params['ad_url'] = "http://".$params['ad_url'];
                    }
                    $params['title'] = json_encode(explode(PHP_EOL, $params['title']));
                     //$params['img'] = json_encode(explode(",", $params['img']));
                    $img=explode(",", $params['img']);
                    $lastimg =[];
                    foreach ($img as $y => &$e) {
                        $imgarr=[];
                        $imgarr['path'] = $e;
                        if(!ckeckHttp($e)){
                            $size = getimagesize(ROOT_PATH."public".$e);
                        }else{
                            $size = getimagesize($e);
                        }
                        $imgarr['width'] = $size['0'];
                        $imgarr['height'] = $size['1'];
                        if(!empty($imgarr)){
                            $lastimg[] = $imgarr;
                        }
                    }
                    $params['img'] = json_encode($lastimg);
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
                   if(!ckeckHttp($params['ad_url'])){
                         $params['ad_url'] = "http://".$params['ad_url'];
                    }
                    $params['title'] = json_encode(explode(PHP_EOL, $params['title']));
                    //$params['img'] = json_encode(explode(",", $params['img']));
                     $img=explode(",", $params['img']);
                    
                     $lastimg =[];
                    foreach ($img as $y => &$e) {
                         $imgarr=[];
                        $imgarr['path'] = $e;
                         if(!ckeckHttp($e)){
                            $size = getimagesize(ROOT_PATH."public".$e);
                        }else{
                            $size = getimagesize($e);
                        }
                        $imgarr['width'] = $size['0'];
                        $imgarr['height'] = $size['1'];
                        if(!empty($imgarr)){
                            $lastimg[] = $imgarr;
                        }
                    }
                    $params['img'] = json_encode($lastimg);
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
        try {
            //$row['img']  =implode(json_decode($row['img'],true),",");
            $img =json_decode($row['img'],true);
            $imgstr = "";
            foreach ($img as $key => $value) {
               $imgstr .=$value['path'].",";
            }
            $row['img'] = rtrim($imgstr,",");
        } catch (\Exception $e) {
          
            $row['img']  = "";
        }
        try {
            $row['title']  =implode(json_decode($row['title'],true),PHP_EOL);
        } catch (Exception $e) {
             $row['title']="";
        }
        $this->view->assign("row", $row);
        $d = Db::name("ad_type")->where(['status'=>1])->column("name","id");
        $this->view->assign("d", $d);
        return $this->view->fetch();
    }
    /**
     * 类型
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function selectpage(){
        $list = Db::name("ad_type")->where(['status'=>1])->field("id,name")->select();
        return json(['list' => $list, 'total' =>count($list)]);
    }
}
