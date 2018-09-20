<?php


namespace app\admin\controller\announce;


use app\common\controller\Backend;

use think\Controller;

use think\Request;

use app\model\AnnounceList as AnnounceListModel;

class Lists extends Backend

{


    protected $model = null;


    public function _initialize()

    {

        parent::_initialize();

        $this->model = new AnnounceListModel();
    }



    public function combineValueToJson($params = [],$key){

        $fieldarr = $valuearr = [];

        if(isset($params[$key]['field'])){
            $field = explode(',',$params[$key]['field']);
            $value = explode(',',$params[$key]['value']);
        }


        foreach ($field as $k => $v)

        {

            if ($v != '')

            {

                $fieldarr[] = $field[$k];

                $valuearr[] = $value[$k];

            }

        }

        $result= json_encode(array_combine($fieldarr, $valuearr), JSON_UNESCAPED_UNICODE);

        return $result;
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

                if(isset($params['title'])){
                    foreach ($params['title'] as $k => &$v)
                    {

                        $v = is_array($v) ? implode(',', $v) : $v;

                    }
                }
                if(isset($params['content'])){
                    foreach ($params['content'] as $k => &$v)
                    {

                        $v = is_array($v) ? implode(',', $v) : $v;

                    }
                }
                if(isset($params['type'])){
                    foreach ($params['type'] as $k => &$v)
                    {

                        $v = is_array($v) ? implode(',', $v) : $v;

                    }
                }

                if ($params['mode'] == 'json')

                {

                    //JSON字段
                    $params['title'] = $this->combineValueToJson($params,'title');
                    $params['type'] = $this->combineValueToJson($params,'type');
                    $params['content'] = $this->combineValueToJson($params,'content');
                    $params['create_time'] = time();

                }

                unset($params['mode']);

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

        return $this->view->fetch();

    }




    /**

     * 编辑

     */

    public function edit($ids = NULL)

    {

        $row = $this->model->get($ids);

        if (!$row)

            $this->error(__('No Results were found'));

        if ($this->request->isPost())

        {

            $params = $this->request->post("row/a");


            if ($params)

            {

                if(isset($params['title'])){
                    foreach ($params['title'] as $k => &$v)
                    {

                        $v = is_array($v) ? implode(',', $v) : $v;

                    }
                }
                if(isset($params['content'])){
                    foreach ($params['content'] as $k => &$v)
                    {

                        $v = is_array($v) ? implode(',', $v) : $v;

                    }
                }
                if(isset($params['type'])){
                    foreach ($params['type'] as $k => &$v)
                    {

                        $v = is_array($v) ? implode(',', $v) : $v;

                    }
                }



                if ($params['mode'] == 'json')

                {

                    //JSON字段
                    $params['title'] = $this->combineValueToJson($params,'title');
                    $params['type'] = $this->combineValueToJson($params,'type');
                    $params['content'] = $this->combineValueToJson($params,'content');
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

        $this->view->assign("row", $row);

        $this->view->assign("announceTitle", (array) json_decode($row->title, true));

        $this->view->assign("announceType", (array) json_decode($row->type, true));

        $this->view->assign("announceContent", (array) json_decode($row->content, true));

        return $this->view->fetch();

    }



}

