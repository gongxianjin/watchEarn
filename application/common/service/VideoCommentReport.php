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
use app\common\logic\VideoCommentReport as VideoCommentReportLogic;
use app\model\CommentVideo as CommentVideoModel;
use app\model\CommentVideoReport;


class VideoCommentReport
{

    /**
     * @param int $id 评论id
     * @param int $userId 用户id
     * @param varchar $content 视频评价内容
     * @throws Exception
     * */

    const V_DUTURE_STATUS  =  1;//视频状态 1 真实  2虚假

    public function report($id,$vid,$userInfo,$du_type){

        if($du_type == self::V_DUTURE_STATUS){

            //举报评论是否存在
            $VideoCommentModel = new VideoCommentLogic($vid);
            $data = $VideoCommentModel->getCommentsByCondition(['id'=>$id]);
            if(empty($data)){
                throw new CommentMessege(['comment is not exisit']);
            }

            $VideoCommentReport = new VideoCommentReportLogic();
            $VideoCommentReport->add(['user_id'=>$userInfo['c_user_id'],"comment_id"=>$id,"video_id"=>$vid,"create_time"=>time()]);

            return true;

        }else{

            //举报评论是否存在
            $data = CommentVideoModel::find($id);
            if(empty($data)){
                throw new CommentMessege();
            }

            $CommentVideoReport = new CommentVideoReport();
            $data = $CommentVideoReport->where(['comment_id'=>$id])->find();
            if(empty($data)){
                $CommentVideoReport->insert(['comment_id'=>$id,"create_time"=>time(),"number"=>1]);
            }else{
                $CommentVideoReport->where(['id'=>$data['id']])->update(['create_time'=>time(),'number'=>['exp',"number+1"]]);
            }
            return true;

        }


    }


}