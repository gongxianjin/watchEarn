<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 18:42
 */

namespace app\common\service;


use app\common\exception\CommentMessege;
use app\common\logic\VideoCommentLogic;
use app\model\CommentUpRecords as CommentUpRecordsModel;
use app\model\CommentVideo as CommentVideoModel;
use app\common\logic\VideoCommentUpRecords as VideoCommetUpRecordsLogic;


class VideoCommentUpRecords
{

    /**
     * @param int $id 评论id
     * @param int $userId 用户id
     * @param varchar $content 视频评价内容
     * @throws Exception
     * */

    const V_DUTURE_STATUS  =  1;//视频状态 1 真实  2虚假

    public function like($id,$vid,$userInfo,$du_type){

        if($du_type == self::V_DUTURE_STATUS){
            //是否点赞过
            $VideoCommentUpRecords = new VideoCommetUpRecordsLogic();
            $where['user_id'] = $userInfo['c_user_id'];
            $where['comment_id'] = $id;
            $is_exists = $VideoCommentUpRecords->findByCondition($where);
            if($is_exists){
                throw new CommentMessege([
                    'msg'=>'like repeat'
                ]);
            }
            $VideoCommentModel = new VideoCommentLogic($vid);

            $data = $VideoCommentModel->getCommentsByCondition(['id'=>$id]);

            if(empty($data)){
                throw new CommentMessege();
            }

            $up['like_count'] = $data['like_count']+1;
            if(!$VideoCommentModel->updateComment(['id'=>$id],$up)){
                throw new CommentMessege([
                    'msg'=>'like error'
                ]);
            }

            //添加到攒点记录
            $VideoCommentUpRecords->add(['user_id'=>$userInfo['c_user_id'],"comment_id"=>$id,"video_id"=>$vid,"create_time"=>time()]);
            $result['count'] = $up['like_count'];
            return $result;

        }else{

            //是否点赞过
            $CommentUpRecords = new CommentUpRecordsModel();
            $is_exists = $CommentUpRecords->where(['user_id'=>$userInfo['c_user_id'],"comment_id"=>$id,"type"=>1])->find();
            if($is_exists){
                throw new CommentMessege([
                    'msg'=>'like repeat'
                ]);
            }
            $data = CommentVideoModel::find($id);
            if(empty($data)){
                throw new CommentMessege();
            }
            $up['like_count'] = $data['like_count']+1;
            if(!CommentVideoModel::update($up,['id'=>$id])){
                throw new CommentMessege([
                    'msg'=>'like error'
                ]);
            }
            //添加到攒点记录
            $CommentUpRecords->insert(['user_id'=>$userInfo['c_user_id'],"comment_id"=>$id,"type"=>1,"create_time"=>time()]);
            $result['count'] = $up['like_count'];
            return $result;

        }


    }




}