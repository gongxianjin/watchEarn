<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;
// 注册路由到index模块的News控制器的read操作
Route::rule('xinwen/sys/login','admin/index/login');

Route::rule('juNew_update.xml','app/ext/update');


//videoCommit
Route::Post('api/video/push_comment','app/VideoComment/push');
Route::get('api/video/list_comment/paginate','app/VideoComment/getList');
//VideoCommentUpRecord
Route::Post('api/video/like_comment','app/VideoComment/CommentLike');
//VideoCommentReport
Route::Post('api/video/report_comment','app/VideoComment/CommentReport');



return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
