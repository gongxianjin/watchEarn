<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:89:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\ad\adsource\add.html";i:1536378849;s:88:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\layout\default.html";i:1536378849;s:85:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\meta.html";i:1536378849;s:87:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\script.html";i:1536378849;}*/ ?>
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
                                <form id="ad-add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <div class="form-group">
        <label for="c-ad_name" class="control-label col-xs-12 col-sm-2">广告名称:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-ad_name" data-rule="required" class="form-control" name="row[ad_name]" type="text" value="">
        </div>
    </div>
    <div class="form-group">
        <label for="c-ad_type_id" class="control-label col-xs-12 col-sm-2">广告平台:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-ad_type_id" data-rule="required" data-source="<?php echo url('/admin/ad/adsource/selectpage'); ?>" class="form-control selectpage" name="row[ad_type_id]" type="text" value="">
        </div>
    </div>
    <div class="form-group">
        <label for="c-materiel_id" class="control-label col-xs-12 col-sm-2">物料id:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-materiel_id" data-rule="required" class="form-control" name="row[materiel_id]" type="text" value="">
        </div>
    </div>
     <div class="form-group">
        <label for="content" class="control-label col-xs-12 col-sm-2">状态:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[status]', ['1'=>'待发布', '2'=>'启用','3'=>'禁用']); ?>
        </div>
    </div>


    <div class="form-group">
        <label for="c-ad_url" class="control-label col-xs-12 col-sm-2">广告地址:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-ad_url" data-rule="required" class="form-control" name="row[ad_url]" type="text" value="">
        </div>
    </div>
    

   
    <div class="form-group">
        <label for="c-title" class="control-label col-xs-12 col-sm-2">推广标题:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-title" class="form-control" data-rule="required" rows="10" name="row[title]"></textarea><span>多个推广标题使用换行区分</span>
        </div>
    </div>
    <div class="form-group">
        <label for="c-avatar" class="control-label col-xs-12 col-sm-2">图片:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-avatar" class="form-control" size="50" data-rule="required" name="row[img]" type="text" value="">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="plupload-avatar" class="btn btn-danger plupload" data-input-id="c-avatar" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp"  data-multiple="true" data-preview-id="p-avatar"><i class="fa fa-upload"></i> 上传</button></span>
                    <span><button type="button" id="fachoose-avatar" class="btn btn-primary fachoose" data-input-id="c-avatar" data-mimetype="image/*" data-multiple="true"><i class="fa fa-list"></i> 选择</button></span>
                </div>
                <span class="msg-box n-right" for="c-avatar"></span>
            </div>
            <ul class="row list-inline plupload-preview" id="p-avatar"></ul>
        </div>
    </div>
   
    <div class="form-group hide layer-footer">
        <label class="control-label col-xs-12 col-sm-2"></label>
        <div class="col-xs-12 col-sm-8">
            <button type="submit" class="btn btn-success btn-embossed disabled"><?php echo __('OK'); ?></button>
            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
        </div>
    </div>

</form>
<script src="http://cdn.demo.fastadmin.net/assets/js/require.js" data-main="http://cdn.demo.fastadmin.net/assets/js/require-backend.min.js?v=1.0.1"></script>



                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="__CDN__/assets/js/require.js" data-main="__CDN__/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>