<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/17
 * Time: 19:06
 */

namespace app\common\service;

use app\common\logic\MusicTypeLogic;
use think\Exception;
class MusicTypeService{




    /**
     * 获取音乐分类
     * ID空 默认全部
     */
    public function getMusicTypeListByID($id = null){

        $where = [];
        if($id){
            $where['id'] = $id;
        }

        $model = new MusicTypeLogic();

        return $model->getMusicTypeList($where);

    }
}