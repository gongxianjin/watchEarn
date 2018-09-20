<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:92:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\film\resources\add.html";i:1536378849;s:88:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\layout\default.html";i:1536378849;s:85:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\meta.html";i:1536378849;s:87:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\script.html";i:1536378849;}*/ ?>
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
                                <form id="add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <div class="form-group">
        <label for="c-name" class="control-label col-xs-12 col-sm-2">视频名称:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-name" data-rule="required" class="form-control" name="row[f_name]" type="text" value="">
        </div>
    </div>
    <div class="form-group">
        <label for="content" class="control-label col-xs-12 col-sm-2">是否推荐:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[f_hot]', ['1'=>'不推荐', '2'=>'推荐']); ?>
        </div>
    </div>
    <div class="form-group">
        <label for="content" class="control-label col-xs-12 col-sm-2">类型:</label>
        <div class="col-xs-12 col-sm-8">
            <?php echo build_radios('row[f_type]', ['film'=>'电影', 'tv_play'=>'视频']); ?>
        </div>
    </div>
    
    <div class="form-group">
        <label for="c-title" class="control-label col-xs-12 col-sm-2">播放平台:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-name" data-rule="required" data-source="<?php echo url('/admin/film/platform/selectpage'); ?>" class="form-control selectpage" name="row[p_type]" type="text" value="">
        </div>
    </div>
  
    <div class="form-group">
        <label for="c-f_flag" class="control-label col-xs-12 col-sm-2">视频标签:</label>
        <div class="col-xs-12 col-sm-8"> 
            <input id="c-f_flag" data-rule="required" class="form-control" name="row[f_flag]" type="text" value="">
        </div>
    </div>
    <div class="form-group">
        <label for="c-image" class="control-label col-xs-12 col-sm-2">视频封面图:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-image" data-rule="" class="form-control" size="50" name="row[f_img]" type="text">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="plupload-image" class="btn btn-danger plupload" data-input-id="c-image" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp" data-multiple="false" data-preview-id="p-image" data-url="<?php echo url('ajax/upload'); ?>"><i class="fa fa-upload"></i> 上传</button></span>
                    <span><button type="button" id="fachoose-image" class="btn btn-primary fachoose" data-input-id="c-image" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> 选择</button></span>
                </div>
                <span class="msg-box n-right" for="c-image"></span>
            </div>
            <ul class="row list-inline plupload-preview" id="p-image"></ul>
        </div>
    </div>
    <div id="extend"></div>
    <div class="form-group">
        <label for="c-director" class="control-label col-xs-12 col-sm-2">导演:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-director" class="form-control" name="row[f_director]" type="text" value="">
        </div>
    </div>
    <div class="form-group">
        <label for="c-performer" class="control-label col-xs-12 col-sm-2">演员:</label>
        <div class="col-xs-12 col-sm-8">
            <input id="c-performer"  class="form-control" name="row[f_performer]" type="text" value="">
        </div>
    </div>
    <div class="form-group">
        <label for="c-value" class="control-label col-xs-12 col-sm-2">视频介绍:</label>
        <div class="col-xs-12 col-sm-8">
            <textarea id="c-value" class="form-control " rows="10" name="row[f_desc]"></textarea>
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