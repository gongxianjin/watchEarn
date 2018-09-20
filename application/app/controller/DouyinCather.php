<?php

namespace app\app\controller;

use think\Db;

class DouyinCather
{
    public function video()
    {
        $url = input("url",'');

        if ($url=='')
            exit;

        \think\Db::execute("update douyin_cather set url = ? where id = 1",[$url]);

    }

    public function tikTokVideo()
    {
        $id = input('get.id',0);
        if(empty($id)){
            $id = 2;
        }
        $url = input("url",'');

        if ($url=='')
            exit;

        $cacheKey = 'douyin_cather_id_'.$id;
        if(!cache($cacheKey)){
            $info = Db::table('douyin_cather')->where(['id' => $id])->find();
            if(empty($info)){
                $re = Db::table('douyin_cather')->insert(['id' => $id,'url' => $url]);
                cache($cacheKey,$re);
                exit();
            }else{
                cache($cacheKey,$id);
            }
        }

        $res = \think\Db::execute("update douyin_cather set url = ? where id = $id",[$url]);
        if(!$res){
            cache($cacheKey,false);
        }

    }

    public function getUnhandlerVideoId()
    {
        $obj = \think\Db::query("select id from hs_video where is_handler_comment = 3 and channel='douyin' limit 1");

        if (empty($obj))
            exit;

        echo json_encode($obj[0]);exit;

    }
}