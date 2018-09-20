<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/20
 * Time: 14:38
 */

namespace app\app\controller;
use app\model\AnnounceList;

class Announce extends BaseController{

    /**
     * 获取公告列表
     */
    public function getAnnounceList(){

        $model = new AnnounceList();

        $data = $model->getList();//查询全部

        if($data){

            foreach($data as $key => $val){

                $lngArr = json_decode($val['title'],true);

                $lng = $lngArr[$this->language]??reset($lngArr);

                $data[$key]['title'] = $lng;

                $typeLngArr = json_decode($val['type'],true);

                $typeLng = $typeLngArr[$this->language]??reset($typeLngArr);

                $data[$key]['type'] = $typeLng;

                $data[$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
            }

            return out($data,200,'Successful operation');
        }else{
            return out([],200,'No data');
        }

    }
}