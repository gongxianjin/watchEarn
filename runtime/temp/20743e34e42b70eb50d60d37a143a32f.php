<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:101:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\user\apprentice\distinctsum.html";i:1536996552;s:88:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\layout\default.html";i:1536378849;s:85:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\meta.html";i:1536378849;s:87:"E:\phpstudy\PHPTutorial\WWW\watchV2\public/../application/admin\view\common\script.html";i:1536378849;}*/ ?>
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
                                <div class="panel panel-default panel-intro">
    <?php echo build_heading(); ?>

    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in">
                <div class="widget-body no-padding">
                    <div class="bootstrap-table">
                        <div class="fixed-table-container" style="padding-bottom: 0px;">
                            <div class="fixed-table-body">
                                <table id="table" class="table table-striped table-bordered table-hover" data-operate-edit="1" data-operate-del="1" width="100%">
                                    <thead>
                                    <tr>
                                        <th style="text-align: center; vertical-align: middle; " data-field="name">
                                            <div class="th-inner ">
                                                名称
                                            </div>
                                            <div class="fht-cell"></div></th>
                                        <th style="text-align: center; vertical-align: middle; " data-field="type">
                                            <div class="th-inner ">
                                                数量
                                            </div>
                                            <div class="fht-cell"></div></th>
                                        <th style="text-align: center; vertical-align: middle; " data-field="operate">
                                            <div class="th-inner ">
                                                操作
                                            </div>
                                        <div class="fht-cell"></div></th>
                                    </tr>
                                    </thead>
                                    <tbody data-listidx="0">
                                    <tr data-index="0" class="">
                                        <td style="text-align: center; vertical-align: middle; ">总徒弟量</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $sontotals; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a target="_blank" href="/admin/user/apprentice/index?ids=<?php echo $user_id; ?>" class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>
                                    <tr data-index="1">
                                        <td style="text-align: center; vertical-align: middle; ">总徒孙量</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $grandsontotals; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a  target="_blank" href="/admin/user/apprentice/index?grandsonids=<?php echo $user_id; ?>"class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>
                                    <tr data-index="2">
                                        <td style="text-align: center; vertical-align: middle; ">与徒弟meid相同个数</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $sonMeidTotals; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a href="/admin/user/apprentice/index?ids=<?php echo $user_id; ?>&meid=<?php echo $meid; ?>" class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>
                                    <tr data-index="3">
                                        <td style="text-align: center; vertical-align: middle; ">与徒弟密码相同个数</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $sonPasswdTotals; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a href="/admin/user/apprentice/index?ids=<?php echo $user_id; ?>&passwd=<?php echo $passwd; ?>" class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>
                                    <tr data-index="4">
                                        <td style="text-align: center; vertical-align: middle; ">此徒弟之间meid相同个数</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $sonTogetherMeidTotals; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a href="/admin/user/apprentice/index?ids=<?php echo $user_id; ?>&type=meid" class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>

                                    <tr data-index="4">
                                        <td style="text-align: center; vertical-align: middle; ">此徒弟之间密码相同个数</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $sonTogetherPasswdTotals; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a href="/admin/user/apprentice/index?ids=<?php echo $user_id; ?>&type=login_passwd" class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>

                                    <tr data-index="4">
                                        <td style="text-align: center; vertical-align: middle; ">此徒弟与徒孙之间meid相同个数</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $sonAndGrandsonMeids; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a href="/admin/user/apprentice/index?ids=<?php echo $user_id; ?>&level=meid&leveltype=1" class="btn btn-info btn-xs btn-detail btn-dialog" title="徒弟详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 徒弟详情</a>
                                            <a href="/admin/user/apprentice/index?ids=<?php echo $user_id; ?>&level=meid&leveltype=0" class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>

                                    <tr data-index="4">
                                        <td style="text-align: center; vertical-align: middle; ">此徒弟与徒孙之间密码相同个数</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $sonAndGrandsonPasswds; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a href="/admin/user/apprentice/index?ids=<?php echo $user_id; ?>&level=login_passwd&leveltype=1" class="btn btn-info btn-xs btn-detail btn-dialog" title="徒弟详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 徒弟详情</a>
                                            <a href="/admin/user/apprentice/index?ids=<?php echo $user_id; ?>&level=login_passwd&leveltype=0" class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>

                                    <tr data-index="4">
                                        <td style="text-align: center; vertical-align: middle; ">此徒孙之间meid相同个数</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $songrandTogetherMeidTotals; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a href="/admin/user/apprentice/index?grandsonids=<?php echo $user_id; ?>&type=meid" class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>

                                    <tr data-index="4">
                                        <td style="text-align: center; vertical-align: middle; ">此徒孙之间密码相同个数</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $subgrandPasswdTogethers; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a href="/admin/user/apprentice/index?grandsonids=<?php echo $user_id; ?>&type=login_passwd" class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>

                                    <tr data-index="4">
                                        <td style="text-align: center; vertical-align: middle; ">此同一ip的其他用户个数</td>
                                        <td style="text-align: center; vertical-align: middle; "><?php echo $ipTotals; ?></td>
                                        <td style="text-align: center; vertical-align: middle; ">
                                            <a href="/admin/user/apprentice/index?ids=<?php echo $user_id; ?>&ip=<?php echo $ip; ?>" class="btn btn-info btn-xs btn-detail btn-dialog" title="详情" data-table-id="table" data-field-index="8" data-row-index="0" data-button-index="0"><i class="fa fa-list"></i> 详情</a>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

    </div>
</div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="__CDN__/assets/js/require.js" data-main="__CDN__/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>