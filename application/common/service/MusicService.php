<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/17
 * Time: 18:49
 */

namespace app\common\service;

use app\common\logic\MusicLogic;
use think\Exception;
use app\model\Music;
class MusicService{

    /**
     * 根据音乐分类ID查找音乐
     */
    public function getMusicListByTid($type_id = null,$page,$pageSize){

        if($type_id){
            $model = new MusicLogic();

            return $model->getMusicList($type_id,$page,$pageSize);

        }else{

            return ['status'=>0,'msg'=>'参数不正确'];
        }

    }


    /**
     * 根据条件查询音乐
     */
    public function getMusicListByWhere($where = [],$page = 1,$pageSize = 20){

        $model = new Music();

        return $model->where($where)->where(['status'=>$model::V_NORMAL_STATUS])->order('"order DESC"')->page($page,$pageSize)->select();

    }






}