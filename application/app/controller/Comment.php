<?php
namespace app\app\controller;


use app\model\Comment as CommentModel;
use think\Db;
use think\Request;

class Comment extends BaseController
{

    public function push()
    {
        $userInfo = &$this->userInfo;

        $request = Request::instance()->param();

        //评论
        $content = '';
        if (isset($request['content']))
            $content = trim($request['content']);
        if ($content===''||mb_strlen($content)>=256)
            return out([],200,'评论错误');

        //文章ID
        $articleId = 0;
        if (isset($request['article_id']))
            $articleId = intval($request['article_id']);
        if ($articleId===0)
            return out([],10002,'文章ID错误');

        $comment = new CommentModel();

        $result = $comment->save([
            'content'=>$content
            ,'user_id'=>$userInfo['c_user_id']
            ,'avatar'=>$userInfo['headimg']
            ,'nickname'=>$userInfo['c_user_id']
            ,'article_id'=>$articleId
        ]);

        if ($result===1)
            return out([],200,'评论成功');
        else
            return out([],10002,'出错了，请重试');

    }

    public function like()
    {
        $request = Request::instance()->param();

        //文章ID
        $commentId = 0;
        if (isset($request['comment_id']))
            $commentId = intval($request['comment_id']);
        if ($commentId===0)
            return out([],10002,'评论ID错误');

        try
        {
            CommentModel::update([
                'like_count'=>['exp','like_count+1']
            ],[
                'id'=>$commentId
            ]);
        }
        catch (\Exception $e)
        {
            return out([],10002,'点赞失败，请重试');
        }

        return out([],200,'点赞成功');
    }

    public function getLikeList()
    {
        $userInfo = &$this->userInfo;

        $count = 20;

        $page = isset($_GET['page']) && intval($_GET['page'])>1?intval($_GET['page']):1;

        $offset = ($page-1)*$count;

        $data = Db::query("
            select avatar,nickname,content,like_count,pub_datetime from hs_comment where user_id=? and like_count != 0 limit {$offset},{$count}
            union all
            select avatar,nickname,content,like_count,pub_datetime from hs_comment_video where user_id=? and like_count != 0 limit {$offset},{$count}
"
            ,[
                $userInfo['c_user_id']
                ,$userInfo['c_user_id']
            ]
        );


        if ($data===null)
            return out([]);
        else
        {
            foreach ($data as &$item)
            {
                $item['pub_datetime'] = date('Y-m-d H:i:s',$item['pub_datetime']);
                $item['user_id'] = $userInfo['c_user_id'];
                $item['avatar'] = $item['avatar']==''? config("default_user_headimg") : $item['avatar'];
            }
            return out($data);
        }

    }

}
