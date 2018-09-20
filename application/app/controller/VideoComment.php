<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/7
 * Time: 15:50
 */

namespace app\app\controller;

use app\common\exception\ParameterException;
use app\common\exception\SuccessMessege;
use app\common\service\VideoCommentReport as VideoCommentReportService;
use app\common\service\VideoCommentUpRecords as VideoCommentUpRecordsService;
use app\common\validate\IDMustBePositiveInt;
use app\common\validate\PagingParameter;
use app\common\validate\VideoComment as VideoCommentValidate;
use app\common\service\VideoComment as VideoCommentService;
use think\Request;

class VideoComment extends BaseController
{

    /**
     * 上传视频评价接口
     * @param int $v_id 视频ID
     */

    public function push()
    {
        //获取评论参数
        $request = $this->params;
        $v_id = $request['v_id'];
        $content = $request['content'];
        //校验参数
        $data['v_id'] = $v_id;
        $data['content'] = $content;

        $validate = new VideoCommentValidate();
        $validate->goCheck($data);

        $userInfo = &$this->userInfo;

        //提交评论
        $comment = new VideoCommentService();

        //判断用户评论视频是虚假还是真实
        $du_type = isset($this->params['du_type'])?$this->params['du_type']:1;

        $comment->SendComment($v_id,$userInfo,$content, $du_type);

        throw new SuccessMessege();
    }

    /**
     * 根据视频ID获取所有的视频评论（分页）
     * @url /list_comment?id=:comment_id&page=:page&size=:page_size
     * @param string $id 视频id
     * @param int $page 分页页数 （可选）
     * @param int $size 每页数量 （可选）
     * @return array of comments
     * @throws ParameterException
     * */

    public function getList($id = '',$page = 1,$size = 10){

        $params = Request::instance()->request();

        (new PagingParameter())->goCheck($params);

        $sort = isset($params['order'])?$params['order']:'time';

        //判断用户评论视频是虚假还是真实
        $du_type = isset($params['du_type'])?$params['du_type']:1;

        $VedioComments = new VideoCommentService();

        $result = $VedioComments->getCommentsByID($id,true,$page,$size,$du_type,$sort,$this->user_id);

        return $result;
    }

    /**
     * 根据评论ID进行点赞
     * @url
     * @param int $commentId 评论id
     * @throws Exception
     * */


    public function CommentLike(){

        $userInfo = &$this->userInfo;
        //获取参数
        $request = $this->params;
        $cid = $request['cid'];
        //校验参数
        $data['id'] = $cid;
        $validate = new IDMustBePositiveInt();
        $validate->goCheck($data);

        //判断用户评论视频是虚假还是真实
        $du_type = isset($this->params['du_type'])?$this->params['du_type']:1;
        $vid = isset($this->params['video_id'])?$this->params['video_id']:'';
        $VedioCommentUpRecords = new VideoCommentUpRecordsService();
        $VedioCommentUpRecords->like($cid,$vid,$userInfo,$du_type);

        throw new SuccessMessege(['msg'=>'like ok!']);
    }


    public function CommentReport(){

        $userInfo = &$this->userInfo;
        //获取参数
        $request = $this->params;
        $cid = $request['cid'];
        //校验参数
        $data['id'] = $cid;
        $validate = new IDMustBePositiveInt();
        $validate->goCheck($data);

        //判断用户评论视频是虚假还是真实
        $du_type = isset($this->params['du_type'])?$this->params['du_type']:1;
        $vid = isset($this->params['video_id'])?$this->params['video_id']:'';

        $VedioCommentReport = new VideoCommentReportService();
        $VedioCommentReport->report($cid,$vid,$userInfo,$du_type);

        throw new SuccessMessege(['msg'=>'report ok!']);

    }







}