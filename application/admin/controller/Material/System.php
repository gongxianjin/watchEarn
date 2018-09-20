<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/20
 * Time: 15:41
 */

namespace app\admin\controller\Material;

use app\common\controller\Backend;


class System extends Backend{


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

}