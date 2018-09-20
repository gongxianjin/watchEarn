<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:90:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\music\bgtype\add.html";i:1537257834;s:88:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\layout\default.html";i:1536378849;s:85:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\meta.html";i:1536378849;s:87:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\script.html";i:1536378849;}*/ ?>
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

    <input type="hidden" name="row[mode]" value="json" />


    <div class="form-group">
        <label for="c-image" class="control-label col-xs-12 col-sm-2">分类封面图:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-image" data-rule="" class="form-control" size="50" name="row[type_cover]" type="text" value="">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="plupload-image" class="btn btn-danger plupload" data-input-id="c-image" data-mimetype="image/gif,image/jpeg,image/png,image/jpg,image/bmp" data-multiple="false" data-preview-id="p-image" data-url="<?php echo url('ajax/upload'); ?>?dir=images"><i class="fa fa-upload"></i> 上传</button></span>
                    <span><button type="button" id="fachoose-image" class="btn btn-primary fachoose" data-input-id="c-image" data-mimetype="image/*" data-multiple="false"><i class="fa fa-list"></i> 选择</button></span>
                </div>
                <span class="msg-box n-right" for="c-image"></span>
            </div>
            <ul class="row list-inline plupload-preview" id="p-image"></ul>
        </div>
    </div>


    <div class="form-group">

        <label for="c-f_sort" class="control-label col-xs-12 col-sm-2">排序:</label>

        <div class="col-xs-12 col-sm-8">

            <input id="c-f_sort" data-rule="required" class="form-control" name="row[order]" type="text" value="" >

        </div>

    </div>

    <div class="form-group">

        <label for="c-value" class="control-label col-xs-12 col-sm-2">分类名称</label>

        <div class="col-xs-12 col-sm-8">


                <dl class="fieldlist" rel="1"  id="c-value" data-name="row" data-listidx="0" nodrag="1">

                <dd>

                    <ins>语种</ins>

                    <ins>名称</ins>

                </dd>

                <dd>

                    <input type="text" name="row[field][1]" class="form-control" id="field-1" value="" size="10" required />

                    <input type="text" name="row[value][1]" class="form-control" id="value-1" value="" size="40" required />

                    <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span>

                </dd>

                <dd><a href="javascript:;" class="append btn btn-sm btn-success"><i class="fa fa-plus"></i> <?php echo __('Append'); ?></a></dd>

            </dl>

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


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="__CDN__/assets/js/require.js" data-main="__CDN__/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>