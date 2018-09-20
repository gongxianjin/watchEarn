<?php
namespace app\model;

/**
 * 用户id
 */
use think\Model;
class UserId extends Model
{
    protected $updateTime = false;
    /**
     * id
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public static function retunCUserId(){
      $UserId = self::create(['create_time'=>time()]);

       return $UserId->id;
    	
    }
 
}
