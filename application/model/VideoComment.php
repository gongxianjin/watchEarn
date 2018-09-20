<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/7
 * Time: 10:53
 */
namespace app\model;

//构造分表评论model
use think\Db;
use think\Model;


class VideoComment extends Model
{

    private static $model;


    /**
     * 根据视频ID初始化评论表
     *
     * @param $videoId
     * @return bool|\think\db\Query
     */
    static function instance($videoId)
    {
        if($videoId === '' || $videoId === false || $videoId === null){
            return false;
        }

        //如果是数字直接分表  否则转换后分表
        if(is_numeric($videoId)){
            $tableNum = abs(intval($videoId % 7));
        }else{
            $hashCode = strToHashInt($videoId);
            $tableNum = abs(intval($hashCode % 7));
        }
        if($tableNum <0 || $tableNum >7){
            return false;
        }


        self::$model = Db::name('video_comment_'.$tableNum);
        return self::$model;
    }



}