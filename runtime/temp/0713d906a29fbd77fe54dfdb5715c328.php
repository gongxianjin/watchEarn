<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:92:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\music\bgmusic\edit.html";i:1537265702;s:88:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\layout\default.html";i:1536378849;s:85:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\meta.html";i:1536378849;s:87:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\script.html";i:1536378849;}*/ ?>
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
                                <form id="add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="" >

    <div class="form-group">

        <label for="m-name" class="control-label col-xs-12 col-sm-2">音乐名称:</label>

        <div class="col-xs-12 col-sm-8">

            <input id="m-name" data-rule="required" class="form-control" name="row[title]" type="text" value="<?php echo $row['title']; ?>">

        </div>

    </div>


    <div class="form-group">

        <label for="type_id" class="control-label col-xs-12 col-sm-2">类型:</label>

        <div class="col-xs-12 col-sm-8">

            <?php echo build_select('row[type_id]', $groupdata, $groupids , ['class'=>'form-control', 'data-rule'=>'required']); ?>

        </div>

    </div>



    <div class="form-group">

        <label for="c-image" class="control-label col-xs-12 col-sm-2">音乐封面图:</label>

        <div class="col-xs-12 col-sm-8">

            <div class="input-group">

                <input id="c-image" data-rule="" class="form-control" size="50" name="row[music_cover]" type="text" value="<?php echo $row['music_cover']; ?>">

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

        <label class="control-label col-xs-12 col-sm-2">音乐:</label>
        <div class="col-xs-12 col-sm-8">
            <div class="input-group">
                <input id="c-vediofile" class="form-control" size="50" name="row[music_url]" type="text" value="<?php echo $row['music_url']; ?>">
                <div class="input-group-addon no-border no-padding">
                    <span><button type="button" id="plupload-vediofile" class="btn btn-danger plupload" data-input-id="c-vediofile" data-mimetype="mp4,mp3,avi,flv,wmv" data-multiple="false" data-maxsize="1024M"  data-url="<?php echo url('ajax/upload'); ?>?dir=audio"><i class="fa fa-upload"></i> 上传</button></span>
                    <span><button type="button" id="fachoose-vediofile" class="btn btn-primary fachoose" data-input-id="c-vediofile" data-mimetype="mp4,mp3,avi,flv,wmv" data-multiple="false"><i class="fa fa-list"></i> 选择</button></span>
                </div>
                <span class="msg-box n-right" for="c-vediofile"></span>
            </div>

        </div>

    </div>



    <div class="form-group">

        <label for="c-f_flag" class="control-label col-xs-12 col-sm-2">音乐时长:</label>

        <div class="col-xs-12 col-sm-8">

            <input id="c-f_flag" data-rule="required" class="form-control" name="row[music_duration]" type="text" value="<?php echo $row['music_duration']; ?>">

        </div>

    </div>

    <div class="form-group">

        <label for="c-f_singer" class="control-label col-xs-12 col-sm-2">歌手:</label>

        <div class="col-xs-12 col-sm-8">

            <input id="c-f_singer" data-rule="required" class="form-control" name="row[music_singer]" type="text" value="<?php echo $row['music_singer']; ?>">

        </div>

    </div>

    <div class="form-group">

        <label for="c-f_sort" class="control-label col-xs-12 col-sm-2">排序:</label>

        <div class="col-xs-12 col-sm-8">

            <input id="c-f_sort" data-rule="required" class="form-control" name="row[order]" type="text" value="<?php echo $row['order']; ?>">

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