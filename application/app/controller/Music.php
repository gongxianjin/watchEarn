<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2018/9/17
 * Time: 18:46
 */

namespace app\app\controller;
use app\common\exception\ParameterException;
use app\common\exception\SuccessMessege;

use app\common\service\MusicService;
use think\Exception;
use think\Request;
use app\common\validate\IDMustBePositiveInt;

class Music extends BaseController{



    /**
     * 分类ID 获取音乐列表
     */
    public function getMusicListByCid(){

        $params =$this->params;

        (new IDMustBePositiveInt())->goCheck($params);

        $page = $params['page']??1;
        $pageSize = $params['page_size']??20;

        //是否存在缓存

        $cache = cache('musicList_'.$params['id'])[$page]??[];

        if($cache){

            $data = $cache;
        }else{

            $model = new MusicService();

            $data = $model->getMusicListByTid($params['id'],$page,$pageSize);
            //设置缓存
            cache('musicList_'.$params['id'],[$page=>$data],['expire'=>60*30]);
        }

        return out($data,'200','Successful operation');
    }


    /**
     * 搜索音乐
     */
    public function searchMusic(Request $request){

        $params = $this->params;
        $keyword = $params['keyword'];
        $page = $params['page']??1;
        $pageSize = $params['page_size']??20;

        if($keyword){

            $where = [];
            $where['title|music_singer'] = ['like','%'.$keyword.'%'];

            $model = new MusicService();

            $data = $model->getMusicListByWhere($where,$page,$pageSize);

            return out($data,200,'Successful operation');

        }else{
            return out([],10001,'Missing keyword');
        }
    }

}