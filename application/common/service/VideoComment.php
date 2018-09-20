<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 18:42
 */

namespace app\common\service;

use app\common\logic\VideoCommentLogic;

use app\model\CommentVideo as CommentVideoModel;

use app\model\Video as VideoModel;

use app\model\UserVideo as UserVideoModel;

use app\model\CommentUpRecords as CommentUpRecordsModel;

use app\model\VideoCommentUpRecords as VideoCommentUpRecordsModel;

use think\Exception;


class VideoComment
{

    /**
     * @param int $vid 视频id
     * @param int $userId 用户id
     * @param varchar $content 视频评价内容
     * @throws Exception
     * */

    const V_DUTURE_STATUS  =  1;//视频状态 1 真实  2虚假

    const V_EXPIRETIME = 120;

    const V_CACHENUM = 200;

    public function SendComment($vid,$userInfo,$content,$du_type){

        if($du_type == self::V_DUTURE_STATUS){

            $VedioCommentModel = new VideoCommentLogic($vid);

            $data = [];
            $data['parent_id'] = 0;
            $data['video_id'] = $vid;
            $data['create_time'] = time();
            $data['content'] = $content;
            $data['user_id'] = $userInfo['c_user_id'];
            $VedioCommentModel->add($data);

            UserVideoModel::update(['comment_count'=>['exp','comment_count+1']],['aliyun_video_id'=>$vid]);

            return true;

        }else{

            $comment = new CommentVideoModel();
            $result = $comment->save([
                'content'=>$content
                ,'user_id'=>$userInfo['c_user_id']
                ,'avatar'=>$userInfo['headimg']
                ,'nickname'=>$userInfo['nickname']
                ,'video_id'=>$vid
            ]);

            VideoModel::update(['comment_count'=>['exp','comment_count+1']],['id'=>$vid]);

            return $result;
        }


    }

    /**
     * 获取某视频下面的评论
     * @param int $vid 视频id
     * @param int $page
     * @param int $size
     * @param boolean $paginate
     * @throws Exception
     * */

    public function getCommentsByID(
        $id,$paginate = true,$page = 1,$size = 10,$du_type,$sort,$user_id
    ){

        if(!in_array($sort, ['up','time'])){
            return out([],10001,"错误请求");
        }

        if($sort=="time" && $du_type == self::V_DUTURE_STATUS){
            $order = "create_time";
        }else if ($sort=="time" && $du_type != self::V_DUTURE_STATUS){
            $order = "pub_datetime";
        }elseif($sort=="up"){
            $order = "like_count";
        }else{
            $order = "id";
        }
        $sort = $order.' DESC';

        //是否存在缓存

        $cache = cache('CommentList');

        if($du_type == self::V_DUTURE_STATUS){

            if($cache){

                $data = $cache;

                return [
                    'code' => 200,
                    'msg' => 'ok',
                    'data' => $data
                ];

            }else{

                $VedioCommentModel = new VideoCommentLogic($id);
                if(!$paginate){
                    $pagingComments =  $VedioCommentModel->getAllCommentsByCondition(['video_id' => $id],'');
                }else{
                    $pagingComments =  $VedioCommentModel->getCommentByCondition(['video_id' => $id],$page,$size,$sort,$user_id);
                }
                $total = $pagingComments->total();
                $commentrecord = $pagingComments
                    ->toArray();
                $data = $commentrecord['data'];
                if ($data===null)
                    return false;
                else
                {
                    //查询是否点赞
                    $idList = [];
                    foreach ($data as $key => $value) {
                        $idList[] = $value['id'];
                    }
                    $CommentUpRecords = new VideoCommentUpRecordsModel();
                    $upMsg = [];
                    if(!empty($idList)){
                        $upMsg = $CommentUpRecords->where(['comment_id'=>['in',$idList],'user_id'=>$user_id,'video_id'=>$id])->column("user_id","comment_id");
                    }
                    foreach ($data as &$item)
                    {
                        if(isset($upMsg[$item['id']])){
                            $item['is_up'] = true;//已赞
                        }else{
                            $item['is_up'] = false;//未赞
                        }
                        if($item['user_id'] == $user_id){
                            $item['is_sure'] = true;//是
                        }else{
                            $item['is_sure'] = false; //不是
                        }
                        $item['pub_datetime'] = $item['create_time'];
                        $item['is_add_gold'] = 1;
                    }

                }

                if($total >=  self::V_CACHENUM){
                    //设置缓存
                    cache('CommentList',$data,['expire'=>self::V_EXPIRETIME]);
                }

                if(empty($pagingComments)){
                    return [
                        'code' => 200,
                        'msg' => 'ok',
                        'data' => []
                    ];
                }
                return [
                    'code' => 200,
                    'msg' => 'ok',
                    'data' => $data
                ];

            }



        }else{

            if($cache){

                $data = $cache;

                return [
                    'code' => 200,
                    'msg' => 'ok',
                    'data' => $data
                ];

            }else {

                $commentrecords = (new CommentVideoModel())
                    ->where('video_id', 'eq', $id)
                    ->order($sort)
                    ->paginate($size, false, ['page' => $page]);
                $commentrecord = $commentrecords
                    ->toArray();
                $data = $commentrecord['data'];
                if ($data === null)
                    return false;
                else {
                    //查询是否点赞
                    $idList = [];
                    foreach ($data as $key => $value) {
                        $idList[] = $value['id'];
                    }
                    $CommentUpRecords = new CommentUpRecordsModel();
                    $upMsg = [];
                    if (!empty($idList)) {
                        $upMsg = $CommentUpRecords->where(['comment_id' => ['in', $idList], 'user_id' => $user_id, 'type' => 1])->column("user_id", "comment_id");
                    }
                    foreach ($data as &$item) {
                        if (isset($upMsg[$item['id']])) {
                            $item['is_up'] = true;//已赞
                        } else {
                            $item['is_up'] = false;//未赞
                        }
                        if ($item['user_id'] == $user_id) {
                            $item['is_sure'] = true;//是
                        } else {
                            $item['is_sure'] = false; //不是
                        }
                        $item['create_time'] = $item['pub_datetime'];
                    }

                }

                $total = $commentrecords->total();
                if($total >=  self::V_CACHENUM){
                    //设置缓存
                    cache('CommentList',$data,['expire'=>self::V_EXPIRETIME]);
                }

                if (empty($commentrecords)) {
                    return [
                        'code' => 200,
                        'msg' => 'ok',
                        'data' => []
                    ];
                }
                return [
                    'code' => 200,
                    'msg' => 'ok',
                    'data' => $data
                ];
            }


        }


    }



}