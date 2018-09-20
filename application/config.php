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

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用命名空间
    'app_namespace'          => 'app',
    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态
    'app_status'             => '',
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 扩展函数文件
    'extra_file_list'        => [THINK_PATH . 'helper' . EXT,APP_PATH . 'common/helpers/functionExt' . EXT],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'PST8PDT',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'index',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => true,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,

    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由
    'url_route_on'           => true,
    // 路由使用完整匹配
    'route_complete_match'   => false,
    // 路由配置文件（支持配置多个）
    'route_config_file'      => ['route'],
    // 是否强制使用路由
    'url_route_must'         => false,
    // 域名部署
    'url_domain_deploy'      => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => true,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,
    // 全局请求缓存排除规则
    'request_cache_except'   => [],

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    // 视图输出字符串内容替换
    'view_replace_str'       => [],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
//    'exception_handle'       => '\think\exception\Handle',
    'exception_handle'       => '\app\common\exception\ExceptionHandler',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => ['info','log','error'],
        // error和sql日志单独记录
        'apart_level'   =>  ['error'],
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace'                  => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                  => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],

    // +----------------------------------------------------------------------
    // | redis配置
    // +----------------------------------------------------------------------
    'redis'                  => [
        'host' => \think\Env::get('redis.host'),
        'port' => \think\Env::get('redis.port'),
        'password' => \think\Env::get('redis.password'),
        'select' => 0,
        'timeout' => 0,
        'expire' => 0,
        //是否长链接
        'persistent' => false,
        'prefix' => '',
    ],
    'redis_backup'                  => [
        'host' => 'r-rj974f9eddbcd694.redis.rds.aliyuncs.com',
        'port' => 6379,
        'password' => '5b397bfcNeb12218e',
        'select' => 0,
        'timeout' => 0,
        'expire' => 0,
        //是否长链接
        'persistent' => false,
        'prefix' => '',
    ],
    'fastadmin'              => [
          'version' => '1.0.0.20171026_beta',
          'api_url' => 'http://api.fastadmin.net',
    ],
    //任务分类
    'active_type' => [
        1=>'日常任务',
        2=>'新手任务',
        3=>'特殊任务',
        4=>'其它任务'
    ],
    "token_str"=>"$#@2017@",//token加密字符串
    "os"=>['web','ios','android'],//设备类型
    "app_sign_key"=>"sdbhybgduewh@$#56656",//ticket加密字符串
    "DATA_CRYPT_TYPE"=>"Think",//加密方式
    "crypt_auth_key"=>"vyuhgbsdyub@dvsd", 
    "ad_domain"=>"http://www.991yue.com",//广告图片图名
    //微信公众号配置 
    "WX_G_HAO_CONFIG"=>[
        "app_id"=>"wxa630ce7ce56092fa",
        "secret"=>"ed64d381499d4a880927af95c095eee7",
    ],
    "WX_PAYMENT"=>[
        //开放平台
        "open_platform"=>[
            'appid'=>"wx66877e4e71d8ea5d",//appidd
            'mchid'=>"1498745852",//商户号
            'secrect_key'=>"292427940292427940292427940ddddc",//api支付秘钥
            'wx_pay_cert_path'=>"/../extend/Payment/wxpay/apiclient_cert.pem",//证书
            'wx_pay_key_path'=>"/../extend/Payment/wxpay/apiclient_key.pem"//证书
        ],
        //微信公众号（易单,YD）
        "yd_platform"=>[
            'appid'=>"wxa630ce7ce56092fa",//appidd
            'mchid'=>"1488256662",//商户号
            'secrect_key'=>"773bc2cbf02de619369cde12b0c7bcee",//api支付秘钥
            'wx_pay_cert_path'=>"/../extend/Payment/yd/apiclient_cert.pem",//证书
            'wx_pay_key_path'=>"/../extend/Payment/yd/apiclient_key.pem"//证书
        ],
    ],
    //APP包名
    "PACKIAHE_NAME"=>[
        "android"=>"com.application.sven.huinews",
        "ios"=>"ios",
        "web"=>"",
    ],
    "AD_POST_URL"=>[
        "dianguan"=>"http://api.aiclk.com/v3/json",//点冠
        "ruishi"=>"http://a.vlion.cn/ssp",//瑞狮

    ],

    "upload_url_host"=>"http://image.hesheng138.com/",
    "upload_path" => ROOT_PATH . 'public/uploads/',

    'http_exception_template'    =>  [
        // 定义404错误的重定向页面地址
        404 =>  APP_PATH.'404.html',
        // 还可以定义其它的HTTP status
    ],
    'recommend_http' => 'http://www.7yse.com',

    //系统接口加密私钥
    'rsa_key' => "-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDunAnWfSj/mOEVSYbiMbpjsdzzCaFPMn3dImxYK5wkWQZ0rijV
1st8bFnySURSVBQIL0fqqQ3Zx8uatqTZ32wrKd0GsU7rlwlwSajv2YYd86cizR3y
U/2SEAOblF77Lj6QEedQerzJvMDvokdK6bhvgZro/+lCXM9DrdsYUU1UrwIDAQAB
AoGAL8sTcWH6f0/Y7dGfcdkyE1wB/LBWHi3n5g5KE1MQ4HrwfxiPV13BvndZgN1K
EQ+EP6twxUD9ZDzPvHqPBJDq8ktURnA52usYf/gJD+z66OUBmKG3ml7q9BMYBM2W
7KrJxVj1UvdwHv63LGNAlQ5fejo1YwsnCK+Y6eM4zfFDz5ECQQD8z0Mt68x5hnFe
iNScE80jVRCr6XwOAFFx5HjAWO8OMPqZuJLdjnLrKwQozGw4bzijOAlsJbc7Wbzn
I1t9g1DXAkEA8Z7mjFUxx4om1APjWNmKhWyUsCYCbC/cFcNm0bBP/z4VCTRM1vuM
S7jYc7VAkqhkD3rRDq6fB5uGqBYfgUgn6QJBANTEx2B9pQDecsnCVVXqoGrNLBPT
lRHfmKxHQo14C/IgrLj1i72mJvffo0eHDMnOaZeNEPkRIQ51bzQFIPoYq88CQEwZ
UOGH/5qqD1qdMuCL+43USexEvGSYmkeceGi4kCmCwxtYBo97QI+k0z92KbVHJeSe
OPPX2ayKtlmARkHwmOkCQHkqFbEasJHYYPcOX0zgdWGdIeO6b/V8U0EGOa/xshwq
aeKv0bbxqw90RrAeEr0gA94awQheyQhfazCTqzrpPes=
-----END RSA PRIVATE KEY-----
",
    //系统接口加密公钥
    "rsa_key_pub" => "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDunAnWfSj/mOEVSYbiMbpjsdzz
CaFPMn3dImxYK5wkWQZ0rijV1st8bFnySURSVBQIL0fqqQ3Zx8uatqTZ32wrKd0G
sU7rlwlwSajv2YYd86cizR3yU/2SEAOblF77Lj6QEedQerzJvMDvokdK6bhvgZro
/+lCXM9DrdsYUU1UrwIDAQAB
-----END PUBLIC KEY-----"
];
