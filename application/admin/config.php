<?php

//配置文件
return [
	 // 视图输出字符串内容替换,留空则会自动进行计算
    'view_replace_str'       => [
        '__PUBLIC__' => '',
        '__ROOT__'   => '',
        '__CDN__'    => '',
    ],
	'controller_suffix'      => false,
    'url_common_param'       => true,
    'url_html_suffix'        => '',
    'controller_auto_search' => true,
     // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'dispatch_jump.tpl',
    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------
    // 异常页面的模板文件
    'exception_tmpl'         => APP_PATH . 'common' . DS . 'view' . DS . 'tpl' . DS . 'think_exception.tpl',

];
