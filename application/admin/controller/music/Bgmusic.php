<?php

namespace app\admin\controller\music;

use app\common\controller\Backend;
use think\Controller;
use think\Request;
use app\model\MusicType as MusicTypeModel;
use app\model\Music as MusicModel;

/**
 * 微信配置管理
 *
 * @icon fa fa-circle-o
 */
class Bgmusic extends Backend
{

    protected $model = null;
    protected $relationSearch = true;

    //当前组别列表数据

    protected $groupdata = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new MusicModel();

        $result = [];

        $result = (new MusicTypeModel())->where(['status'=>'normal'])->field('id,name')->select();

        $groupName = [];

        foreach ($result as $k => $v)
        {

            $groupName[$v['id']] = $v['name'];

        }
        $this->groupdata = $groupName;

        $this->view->assign('groupdata', $this->groupdata);

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
                    ->join("music_type v","a.type_id = v.id")
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->alias('a')
                    ->join("music_type v","a.type_id = v.id")
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->field('a.*,v.name')
                    ->select();

            if(!empty($list)){
                if(!empty($list)){
                    foreach ($list as $key => &$value) {
                        $value['id'] = strval($value['id']);
                        $value['create_time'] = date('Y-m-d H:i',$value['create_time']);
                        $value['music_duration'] = date('i:s',$value['music_duration']);
                    }
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

            $params['music_url'] = Config('upload_url_host').$params['music_url'];
            $params['music_cover'] = Config('upload_url_host').$params['music_cover'];

            $paramstimes = [];

            if($params['music_duration']){

                $paramstimes = explode(':',$params['music_duration']);

                $params['music_duration'] = $paramstimes[0] * 60 + $paramstimes[1];
            }

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
                        cache('musicList_'.$params['type_id'],null);
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

        $row = $this->model
                ->alias("a")
                ->join("music_type v","a.type_id = v.id")
                ->where(['a.id'=>$ids])
                ->field("a.*,v.name")
                ->find();
        
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost())
        {

            $params = $this->request->post("row/a");
            if(strpos($params['music_cover'],Config('upload_url_host')) === false){
                $params['music_cover'] = Config('upload_url_host').$params['music_cover'];
            }

            if(strpos($params['music_url'],Config('upload_url_host')) === false){
                $params['music_url'] = Config('upload_url_host').$params['music_url'];
            }


            if ($params)
            {
                foreach ($params as $k => &$v)
                {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }

                $paramstimes = [];

                if($params['music_duration']){

                    $paramstimes = explode(':',$params['music_duration']);

                    $params['music_duration'] = $paramstimes[0] * 60 + $paramstimes[1];
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

                    $result = $this->model->where(['id'=>$ids])->update($params);
                    if ($result !== false)
                    {
                        cache('musicList_'.$params['type_id'],null);
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

        $row['music_duration'] = intval($row['music_duration'] / 60).":". $row['music_duration'] % 60;

        $this->view->assign("row", $row);
        $this->view->assign("groupids", $row['type_id']);
        return $this->view->fetch();
    }
    

    /**
     * 播放
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */

    public function play($ids=null){
        $row =  (new MusicModel())->where(['id'=>$ids])->find();
        if (!$row)
            $this->error(__('No Results were found'));
        $this->assign("row", $row);
        
         return $this->view->fetch();

    }

}
