<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/17
 * Time: 19:07
 */

namespace app\common\logic;
use app\model\MusicType;
class MusicTypeLogic{

    /**
     * 根据条件查询列表
     * where = [];
     */

    public function getMusicTypeList($where = []){

        $model = new MusicType();

        return $model->getList($where);
    }


}