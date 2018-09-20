<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/17
 * Time: 9:37
 */


namespace app\admin\controller\images;

use app\common\controller\Backend;
use think\Db;

use app\model\Images as ImagesModel;

class Images extends Backend{

    protected $model = 'images';

    public function _initialize(){
        parent::_initialize();
        $this->model = new ImagesModel();
    }


    /**
     * 列表
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

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }



        return $this->fetch();
    }


    /**
     * 上传图片
     */
    public function add(){


        if($this->request->isPost()){

            $params = $this->request->post("row/a");

            //验证图片名称是否是json字符串格式

            $res = $this->isJson($params['ad_name']);

            if($res == false){
                $this->error('图片名称填写有误,请参照'.'{"en":"images","cn":"图片"}');
            }

            $data['name'] = $params['ad_name'];
            $data['status'] = $params['status'];
            //图片访问地址
            $data['url'] = config('upload_url_host').$params['img'];

            $result = $this->model->save($data);

            if($result !== false){

                $this->success();
            }else{
                $this->error($this->model->getError());
            }

        }

        return $this->fetch();

    }


    /**
     * 删除图片
     */
    public function del($ids = NULL){


        if($ids){
            $res = $this->model->destroy($ids);

            if($res){
                $this->success();
            }else{
                $this->error($this->model->getError());
            }
        }
    }


    /**
     * 修改图片
     */
    public function edit($ids = null){

        $info = $this->model->where(['id'=>$ids])->find();

        if ($this->request->isPost()){

            $params = $this->request->post("row/a");

            //验证图片名称是否是json字符串格式
            $res = $this->isJson($params['name']);

            if($res == false){
                $this->error('图片名称填写有误,请参照'.'{"en":"images","cn":"图片"}');
            }

            if(strpos($params['img'],Config('upload_url_host')) === false){
                $params['img'] = Config('upload_url_host').substr($params['img'],1);

            }

            $data['name'] = $params['name'];
            $data['status'] = $params['status'];
            //图片访问地址
            $data['url'] = $params['img'];

            $where['id'] = $ids;

            $result = $this->model->where($where)->update($data);

            if($result !== false){
                $this->success();
            }else{
                $this->error($this->model->getError());
            }

        }

        $this->assign('row',$info);
        return $this->fetch();
    }

    private function isJson($data = '') {
        $data = json_decode($data, true);
        if (is_array($data) && !empty(current($data))) {
            return $data;
        }
        return false;
    }
    
}