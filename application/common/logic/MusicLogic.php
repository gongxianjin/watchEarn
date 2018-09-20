<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/17
 * Time: 18:52
 */

namespace app\common\logic;
use app\model\Music;

class MusicLogic{

    public function getMusicList($type_id,$page,$pageSize){

        $model = new Music();

        return $model->getLists($type_id,$page,$pageSize);
    }

}