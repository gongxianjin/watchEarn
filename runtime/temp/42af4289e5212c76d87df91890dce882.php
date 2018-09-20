<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:85:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\addon\index.html";i:1536378849;s:88:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\layout\default.html";i:1536378849;s:85:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\meta.html";i:1536378849;s:87:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\script.html";i:1536378849;}*/ ?>
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
                                <style type="text/css">
    .item-addon{margin-left:15px;margin-bottom:15px;}
    .item-addon img.img-responsive,.item-addon .noimage{width: 300px;height:200px;}
    .noimage {line-height: 200px;text-align: center;background:#18bc9c;color:#fff;}
    .addon {position: relative;}
    .addon > span {position:absolute;left:15px;top:15px;}
    .layui-layer-pay .layui-layer-content {padding:0;height:600px!important;}
    .layui-layer-pay {border:none;}
    .payimg{position:relative;width:800px;height:600px;}
    .payimg .alipaycode {position:absolute;left:265px;top:442px;}
    .payimg .wechatcode {position:absolute;left:660px;top:442px;}
</style>
<div id="warmtips" class="alert alert-dismissable alert-danger hide">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong><?php echo __('Warning'); ?></strong> <?php echo __('Https tips'); ?>
</div>
<div class="panel panel-default panel-intro">
    <?php echo build_heading(); ?>

    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">
                    <div id="toolbar" class="toolbar">
                        <?php echo build_toolbar('refresh'); ?>
                        <button type="button" id="plupload-addon" class="btn btn-danger plupload" data-url="addon/local" data-mimetype="application/zip" data-multiple="false"><i class="fa fa-upload"></i> <?php echo __('Offline install'); ?></button>
                        <a class="btn btn-success btn-ajax" href="addon/refresh"><i class="fa fa-refresh"></i> <?php echo __('Refresh addon cache'); ?></a>
                        <!-- <a class="btn btn-info btn-switch btn-store" href="javascript:;" data-url="<?php echo $config['fastadmin']['api_url']; ?>/addon/index"><i class="fa fa-cloud"></i> <?php echo __('Online store'); ?></a> -->
                        <a class="btn btn-info btn-switch" href="javascript:;" data-url="addon/downloaded"><i class="fa fa-laptop"></i> <?php echo __('Local addon'); ?></a>
                        <a class="btn btn-primary btn-userinfo" href="javascript:;"><i class="fa fa-user"></i> <?php echo __('Userinfo'); ?></a>
                    </div>
                    <table id="table" class="table table-striped table-hover" width="100%">

                    </table>

                </div>
            </div>

        </div>
    </div>
</div>
<script id="logintpl" type="text/html">
    <div>
        <form class="form-horizontal">
            <fieldset>
                <div class="alert alert-dismissable alert-danger">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong><?php echo __('Warning'); ?></strong><br /><?php echo __('Login tips'); ?>
                </div>
                <div class="form-group">
                    <label for="inputAccount" class="col-lg-3 control-label"><?php echo __('Username'); ?></label>
                    <div class="col-lg-9">
                        <input type="text" class="form-control" id="inputAccount" value="" placeholder="<?php echo __('Your username or email'); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputPassword" class="col-lg-3 control-label"><?php echo __('Password'); ?></label>
                    <div class="col-lg-9">
                        <input type="password" class="form-control" id="inputPassword" value="" placeholder="<?php echo __('Your password'); ?>">
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</script>
<script id="userinfotpl" type="text/html">
    <div>
        <form class="form-horizontal">
            <fieldset>
                <div class="alert alert-dismissable alert-success">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong><?php echo __('Warning'); ?></strong><br /><?php echo __('Logined tips', '<%=username%>'); ?>
                </div>
            </fieldset>
        </form>
    </div>
</script>
<script id="paytpl" type="text/html">
    <div class="payimg" style="background:url('<%=payimg%>') 0 0 no-repeat;background-size:cover;">
        <%if(paycode){%>
        <div class="alipaycode">
            <%=paycode%>
        </div>
        <div class="wechatcode">
            <%=paycode%>
        </div>
        <%}%>
    </div>
</script>
<script id="conflicttpl" type="text/html">
    <div class="alert alert-dismissable alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong><?php echo __('Warning'); ?></strong> <?php echo __('Conflict tips'); ?>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th><?php echo __('File'); ?></th>
            </tr>
        </thead>
        <tbody>
            <%for(var i=0;i < conflictlist.length;i++){%>
            <tr>
                <th scope="row"><%=i+1%></th>
                <td><%=conflictlist[i]%></td>
            </tr>
            <%}%>
        </tbody>
    </table>
</script>
<script id="itemtpl" type="text/html">
    <div class="item-addon">
        <% var labelarr = ['primary', 'success', 'info', 'danger', 'warning']; %>
        <% var label = labelarr[item.id % 5]; %>
        <% var addon = typeof addons[item.name]!= 'undefined' ? addons[item.name] : null; %>
        <div class="thumbnail addon">
            <!--<span class="btn btn-<%=label%>">ID:<%=item.id%></span>-->
            <a href="<%=addon?addon.url:'javascript:;'%>" target="_blank">
                <%if(item.image){%>
                <img src="<%=item.image%>" class="img-responsive" alt="<%=item.title%>">
                <%}else{%>
                <div class="noimage"><?php echo __('No image'); ?></div>
                <%}%>
            </a>
            <div class="caption">
                <h4><%=item.title?item.title:'<?php echo __('None'); ?>'%>
                    <% if(item.flag.indexOf("recommend")>-1){%>
                    <span class="label label-success"><?php echo __('Recommend'); ?></span>
                    <% } %>
                    <% if(item.flag.indexOf("hot")>-1){%>
                    <span class="label label-danger"><?php echo __('Hot'); ?></span>
                    <% } %>
                    <% if(item.flag.indexOf("free")>-1){%>
                    <span class="label label-info"><?php echo __('Free'); ?></span>
                    <% } %>
                    <% if(item.flag.indexOf("sale")>-1){%>
                    <span class="label label-warning"><?php echo __('Sale'); ?></span>
                    <% } %>
                </h4>
                <p class="text-<%=item.price>0?'danger':'success'%>"><b>￥<%=item.price%></b></p>
                <p class="text-muted"><?php echo __('Author'); ?>: <a href="<%=item.url?item.url:'javascript:;'%>" target="_blank"><%=item.author%></a></p>
                <p class="text-muted"><?php echo __('Intro'); ?>: <%=item.intro%></p>
                <p class="text-muted"><?php echo __('Version'); ?>: <%=# addon && item && addon.version!=item.version?'<span class="label label-danger">'+addon.version+'</span> -> <span class="label label-success">'+item.version+'</span>':item.version%></p>
                <p class="text-muted"><?php echo __('Createtime'); ?>: <%=Moment(item.createtime*1000).format("YYYY-MM-DD HH:mm:ss")%></p>
                <p class="operate" data-id="<%=item.id%>" data-name="<%=item.name%>">
                    <% if(!addon){ %>
                    <a href="javascript:;" class="btn btn-primary btn-success btn-install" data-type="<%=item.price<=0?'free':'price';%>" data-donateimage="<%=item.donateimage%>"><i class="fa fa-cloud-download"></i> <?php echo __('Install'); ?></a>
                    <% if(item.demourl){ %>
                    <a href="<%=item.demourl%>" class="btn btn-primary btn-info btn-demo" target="_blank"><i class="fa fa-flash"></i> <?php echo __('Demo'); ?></a>
                    <% } %>
                    <% } %>

                    <% if(addon){ %>
                    <% if(addon.config){ %>
                    <a href="javascript:;" class="btn btn-primary btn-config"><i class="fa fa-pencil"></i> <?php echo __('Setting'); ?></a>
                    <% } %>
                    <% if(addon.state == "1"){ %>
                    <a href="javascript:;" class="btn btn-warning btn-disable" data-action="disable"><i class="fa fa-times"></i> <?php echo __('Disable'); ?></a>
                    <% }else{ %>
                    <a href="javascript:;" class="btn btn-success btn-enable" data-action="enable"><i class="fa fa-check"></i> <?php echo __('Enable'); ?></a>
                    <a href="javascript:;" class="btn btn-danger btn-uninstall"><i class="fa fa-times"></i> <?php echo __('Uninstall'); ?></a>
                    <% } %>
                    <% } %>
                    <!--
                    <span class="pull-right" style="margin-top:10px;">
                        <input name="checkbox" data-id="<%=item.id%>" type="checkbox" />
                    </span>
                    -->
                </p>
            </div>
        </div>
    </div>
</script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="__CDN__/assets/js/require.js" data-main="__CDN__/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>