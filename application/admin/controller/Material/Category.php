<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/20
 * Time: 16:06
 */

namespace app\admin\controller\Material;
use app\common\controller\Backend;
use app\model\MaterialCategory;
use think\Request;

class Category extends Backend {


    public function index(){

        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {

            $model = new MaterialCategory();

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('pkey_name')) {

                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $model->where($where)->order($sort, $order)->count();

            $list = $model->where($where)->order($sort, $order)->limit($offset, $limit)->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        return $this->fetch();
    }


    public function add(){

        if($this->request->isPost()){

            $params = $this->request->post("row/a");

            $res = json_decode($params['name'], true);

            if(is_array($res)) {

                $model = new MaterialCategory();
                $result = $model->save($params);

                if($result){
                    $this->success();
                }else{
                    $this->error($model->getError());
                }
            }else{
                $this->error('图片名称填写有误,请参照'.'{"en":"images","cn":"图片"}');
            }

        }

        return $this->fetch();
    }

    public function edit($ids = null){

        $model = new MaterialCategory();
        $info = $model->where(['id'=>$ids])->find();

        if ($this->request->isPost()) {

            $params = $this->request->post("row/a");

            $res = json_decode($params['name'], true);

            if (is_array($res)) {



                $where['id'] = $ids;

                $result = $model->where($where)->update($params);

                if ($result) {
                    $this->success();
                } else {
                    $this->error($model->getError());
                }
            } else {
                $this->error('图片名称填写有误,请参照' . '{"en":"images","cn":"图片"}');
            }
        }

        $this->assign('row',$info);
        return $this->fetch();
    }

    public function del($ids = null){

        if($ids){
            $model = new MaterialCategory();

            $res = $model->destroy($ids);

            if($res){
                $this->success();
            }else{
                $this->error($model->getError());
            }
        }
    }

}