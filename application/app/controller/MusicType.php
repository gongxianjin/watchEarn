<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/17
 * Time: 19:04
 */

namespace app\app\controller;
use app\common\exception\ParameterException;
use app\common\exception\SuccessMessege;

use app\common\service\MusicTypeService as MusicTypeService;
use think\Exception;
use think\Request;
use app\common\validate\IDMustBePositiveInt;


class MusicType extends BaseController {


    /**
     * 获取音乐全部分类
     *
     * 也可传入ID 指定分类
     */
    public function getMusicTypeList(){

//        $params = Request::instance()->param();
//
//        (new IDMustBePositiveInt())->goCheck($params);


        $cache =cache('musicType');

        if($cache){

            $data = $cache;
        }else{

            $model = new MusicTypeService();

            $data = $model->getMusicTypeListByID();

            foreach($data as $key => $val){

                $lngArr = json_decode($val['name'],true);

                $lng = $lngArr[$this->language]??reset($lngArr);

                $data[$key]['name'] = $lng;
            }

            //设置缓存
            cache('musicType',$data,['expire'=>60*30]);
        }

        return out($data,'200','Successful operation');
    }

}