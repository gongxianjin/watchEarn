<?php
namespace app\app\controller;

use think\Db;
use app\model\VideoReport;
use app\model\CommentVideoReport;

class Report extends BaseController
{
	/**
	 * 视频举报
	 * @param  模型，引用传递
	 * @param  查询条件
	 * @param int  每页查询条数
	 * @return 返回
	 */
    public function video()
    {
        $params = $this->params;
      	$video_id = isset($params['id']) ? $params['id'] : 0;
      	$du_type = isset($params['du_type']) ? $params['du_type'] : 2;
       	if(empty($video_id)){
      		return out([],10001,"错误请求");
      	}
      	$VideoReport = new VideoReport();
      	$data = $VideoReport->where(['video_id'=>$video_id,'du_type' => $du_type])->find();
      	if(empty($data)){
      		$VideoReport->insert(['video_id'=>$video_id,"create_time"=>time(),'du_type' => $du_type,"number"=>1]);
      	}else{
      		$VideoReport->where(['id'=>$data['id']])->update(['create_time'=>time(),'number'=>['exp',"number+1"]]);
      	}
      	return  out([],200,"success");

    }
    /**
     * 评论举报
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function videocomment()
    {
      	$comment_id = input("id/s");
      	if(empty($comment_id)){
      		return out([],10001,"错误请求");
      	}
      	$CommentVideoReport = new CommentVideoReport();
      	$data = $CommentVideoReport->where(['comment_id'=>$comment_id])->find();
      	if(empty($data)){
      		$CommentVideoReport->insert(['comment_id'=>$comment_id,"create_time"=>time(),"number"=>1]);
      	}else{
      		$CommentVideoReport->where(['id'=>$data['id']])->update(['create_time'=>time(),'number'=>['exp',"number+1"]]);
      	}
      	return  out([],200,"success");

    }

}
