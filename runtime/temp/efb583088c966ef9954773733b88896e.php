<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:96:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\examine\recharge\index.html";i:1536817025;s:88:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\layout\default.html";i:1536378849;s:85:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\meta.html";i:1536378849;s:87:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\script.html";i:1536378849;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:'新闻管理'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<link rel="shortcut icon" href="__CDN__/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="__CDN__/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="__CDN__/assets/js/html5shiv.js"></script>
  <script src="__CDN__/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>
    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <div class="panel panel-default panel-intro">    <?php echo build_heading(); ?>    <div class="panel-body">    <form id="add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="" >        <div class="form-group">            <label for="recharge_email" class="control-label col-xs-12 col-sm-2">充值账号邮箱:</label>            <div class="col-xs-12 col-sm-8">                <input  data-rule="required" class="form-control" name="recharge_email" type="text" value="">            </div>        </div>        <div class="form-group">            <label for="recharge_num" class="control-label col-xs-12 col-sm-2">充值金币数量:</label>            <div class="col-xs-12 col-sm-8">                <input  data-rule="required" class="form-control" name="recharge_num" type="number" value="">            </div>        </div>        <div class="form-group">            <label for="recharge_desc" class="control-label col-xs-12 col-sm-2">充值备注（用户可见）:</label>            <div class="col-xs-12 col-sm-8">                <input  data-rule="required" class="form-control" name="recharge_desc" type="text" value="">            </div>        </div>        <div class="form-group">            <label for="recharge_password" class="control-label col-xs-12 col-sm-2">充值密码:</label>            <div class="col-xs-12 col-sm-8">                <input  data-rule="required" class="form-control" name="recharge_password" type="password" value="">            </div>        </div>        <div class="form-group">            <label class="control-label col-xs-12 col-sm-2"></label>            <div class="col-xs-12 col-sm-8">                <button type="button" onclick="checkSure()" class="btn btn-success btn-embossed"><?php echo __('OK'); ?></button>                <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>            </div>        </div>    </form>    </div></div><script>    function checkSure()    {        var email = $("input[name=recharge_email]").val();        var num = $("input[name=recharge_num]").val();        var desc = $("input[name=recharge_desc]").val();        var pwd = $("input[name=recharge_password]").val();        if(!email){            layer.msg("请输入充值账号邮箱");            return false;        }        if(!num){            layer.msg("请输入充值金币数量");            return false;        }        if(!desc){            layer.msg("请输入充值备注");            return false;        }        if(!pwd){            layer.msg("请输入充值密码");            return false;        }        var data = {email:email,num:num,desc:desc,pwd:pwd};        layer.confirm("确定给充值吗？",['充值',"取消"],function () {            layer.closeAll();            $.post(location.href,data,function (json) {                var retData = json;                layer.msg(retData.msg);                if(retData.code == 200){                    setTimeout(function () {                        location.href = location.href;                    },1000);                }            })        },function () {            layer.closeAll();        });    }</script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="__CDN__/assets/js/require.js" data-main="__CDN__/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>