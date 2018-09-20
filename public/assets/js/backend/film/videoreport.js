define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'film/videoreport/index',
                    add_url: 'film/videoreport/add',
                    edit_url: 'film/videoreport/edit',
                    del_url: 'film/videoreport/del',
                    multi_url: 'film/videoreport/multi',
                    table: 'wechat_config',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
               // sortName: 'id',
               // sortName: 'visit_count',
                columns: [
                    [
                        {field: '', checkbox: true},
                        {field: 'id', title:"ID",sortable: true},
                        {field: 'title', title:"视频标题",formatter: Controller.api.formatter.short_desc},
                        {field: 'channel', title:"采集渠道"},
                        {field: 'create_time', title:"添加時間",sortable: true},
                        {field: 'video_cover', title:"封面图",formatter: Controller.api.formatter.thumb},
                        {field: 'id', title: __('按钮'), table: table, buttons: [
                            {name: 'detail', text: '播放', title: '播放', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'film/videoreport/play'},
                        ], operate:false, formatter: Table.api.formatter.buttons},
                        {field: 'status', title:"状态",sortable:true, formatter: Controller.api.formatter.f_hot},
                        {field: 'e_count', title:"错误播放次数",sortable: true},
                        {field: 'operate', title: __('Operate'), table: table,  buttons: [
                            ],events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                $(document).on('click', ".btn-jsoneditor", function () {
                    $("#c-value").toggle();
                    $(".fieldlist").toggleClass("hide");
                    $(".btn-insertlink").toggle();
                    $("input[name='row[mode]']").val($("#c-value").is(":visible") ? "textarea" : "json");
                });
             
                $(document).on('click', "input[name='row[f_type]']", function () {
                    var type = $(this).val();
                    /*if (type == 'film') {
                        $("#expand").html('');
                    } else {
                        $("#expand").html('<div class="form-group"><label for="c-f_number" class="control-label col-xs-12 col-sm-2">总集数:</label><div class="col-xs-12 col-sm-8"> <div class="input-group margin-bottom-sm"><input id="c-f_number" data-rule="required" class="form-control" name="row[f_number]" placeholder="总集数" type="number" value="0"> <span class="input-group-addon"><i class="fa fa-eye text-success"></i></span></div></div> </div>');
                    }*/
                });

                $(document).on("click", ".fieldlist .append", function () {
                    var rel = parseInt($(this).closest("dl").attr("rel")) + 1;
                    $(this).closest("dl").attr("rel", rel);
                    $('<dd><input type="text" name="field[' + rel + ']" class="form-control" id="field-' + rel + '" value="" size="10" /> <input type="text" name="value[' + rel + ']" class="form-control" id="value-' + rel + '" value="" size="40" /> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span></dd>').insertBefore($(this).parent());
                });
                $(document).on("click", ".fieldlist dd .btn-remove", function () {
                    $(this).parent().remove();
                });

                //拖拽排序
                require(['dragsort'], function () {
                    //绑定拖动排序
                    $("dl.fieldlist").dragsort({
                        itemSelector: 'dd',
                        dragSelector: ".btn-dragsort",
                        dragEnd: function () {

                        },
                        placeHolderTemplate: "<dd></dd>"
                    });
                });
            },
            formatter: {
                thumb: function (value, row, index) {
                        return '<a href="' + row.video_cover + '" target="_blank"><img src="' + row.video_cover+'" alt="" style="max-height:90px;max-width:120px"></a>';
                   
                },
                url: function (value, row, index) {
                    return '<a href="' + value + '" target="_blank" class="label bg-green">' + value + '</a>';
                },
                short_desc:function(value,row,index){
                    if(value.length>10){
                        return value.substring(0,10)+"...";
                    }else{
                        return value;
                    }
                },
                f_hot: function (value, row, index) {
                    console.log(value);     
                    if(value == 2){
                     return '<span class="label label-danger">禁用</span>';
                    }else{
                        return '<span class="label label-info">正常</span>';
                    }
                },
                /*play: function (value, row, index) {
                    return "<a href='film/videoreport/index'>点击播放</a>";
                },*/
                
            }
        }
    };
    return Controller;
});