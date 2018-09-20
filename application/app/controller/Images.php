<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/17
 * Time: 16:43
 */

namespace app\app\controller;

use app\model\Images as ImagesModel;
class Images extends BaseController {


    public function getImagesList(){


        $model = new ImagesModel();

        $data = $model->select();

        $res_data = [];
        foreach($data as $val){

            $res_data[] = $val->toArray();
        }

        return out($res_data,'200','Successful operation');
    }
}