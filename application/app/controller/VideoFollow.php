<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/7
 * Time: 15:50
 */

namespace app\app\controller;

use app\common\service\VideoFollow as VideoFollowService;
use app\common\validate\IDMustBePositiveInt;

class VideoFollow extends BaseController
{

    /**
     * 关注视频用户接口
     * @param int $v_id 视频ID
     */

    public function followUser($v_id)
    {
        //校验参数
        $data['id'] = $v_id;
        $validate = new IDMustBePositiveInt();
        $validate->goCheck($data);
        $user_id = $this->user_id;
        exit;
    }

    /**
     * 根据视频ID获取所有的视频评论（分页）
     * @url /list_comment?id=:comment_id&page=:page&size=:page_size
     * @param int $id 视频id
     * @param int $page 分页页数 （可选）
     * @param int $size 每页数量 （可选）
     * @return array of comments
     * @throws ParameterException
     * */
    public function getList($id = -1,$page = 1,$size = 10){

        $params = Request::instance()->param();

        (new IDMustBePositiveInt())->goCheck($params);
        (new PagingParameter())->goCheck($params);

        $VedioComments = new VideoCommentService();
        $pagingComments = $VedioComments->getCommentsByID($id,true,$page,$size);

        if(empty($pagingComments)){

            return [
                'current_page' => $pagingComments->currentPage(),
                'data' =>[]
            ];

        }

        $data = $pagingComments
            ->toArray();

        return [
            'current_page' =>$pagingComments->currentPage(),
            'data' =>$data['data']
        ];

    }




}