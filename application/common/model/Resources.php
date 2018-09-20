<?php

namespace app\common\model;

use think\Model;

class Resources extends Model
{

    /**
     * 数据获取
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
   public static function getlist($map=[]){

        //$map['f_hot'] = 2;
        $list = self::where($map)->order("id DESC")->select();
        return $list;
   }
   /**
    * 获取详细信息
    * @param  模型，引用传递
    * @param  查询条件
    * @param int  每页查询条数
    * @return 返回
    */
   public  function  getFirst($id){
        $data = self::where(['id'=>$id])->find();
        return empty($data)?[]:$data;
   }
   /**
    * 关联查询
    * @param  模型，引用传递
    * @param  查询条件
    * @param int  每页查询条数
    * @return 返回
    */
   public  function play(){
        return $this->hasMany('ResourcesPlay','r_id');
    }

}
