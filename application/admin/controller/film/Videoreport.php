<?php

namespace app\admin\controller\film;

use app\common\controller\Backend;
use think\Controller;
use think\Request;
use app\model\Video;
use app\model\VideoErrorRecord;

/**
 * 微信配置管理
 *
 * @icon fa fa-circle-o
 */
class Videoreport extends Backend
{

    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new VideoErrorRecord();
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
                ->alias('a')
                ->join("__VIDEO__ v","a.video_id = v.id")
                ->where($where)
                ->order($sort, $order)
                ->count();
        $list = $this->model
                ->alias('a')
                ->join("__VIDEO__ v","a.video_id = v.id")
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->field("v.*,a.e_count")
                ->select();
        if(!empty($list)){
            if(!empty($list)){
                foreach ($list as $key => &$value) {
                    $value['id'] = strval($value['id']);
                    $value['create_time'] = date('Y-m-d H:i',$value['create_time']);
                }
            }
        }

        $result = array("total" => $total, "rows" => $list);
        return json($result);
    }
    return $this->view->fetch();
}
   

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {

        $row = $this->model
                ->alias("a")
                ->join("__VIDEO__ V","a.video_id = v.id")
                ->where(['a.video_id'=>$ids])
                ->field("v.*,a.id as err_id")
                ->find();
        
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
                    if($params['status'] == 2){
                        $params['dis_time'] = time();
                    }

                    $result = (new Video())->where(['id'=>$ids])->update($params);
                    if ($result !== false)
                    {
                        //
                        $this->model->where(['id'=>$row['err_id']])->delete();
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

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    
    /**
     * 删除
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function del($ids=null){
        if($this->model->where(['video_id'=>$ids])->delete()){
            $this->success();
        }else{
             $this->error("删除失败");
        }
       
    }
    /**
     * 播放
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function play($ids=null){
        $row =  (new Video())->where(['id'=>$ids])->find();
        if (!$row)
            $this->error(__('No Results were found'));
        $this->assign("row", $row);
        
         return $this->view->fetch();

    }

}
