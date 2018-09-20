define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'music/bgmusic/index',
                    add_url: 'music/bgmusic/add',
                    edit_url: 'music/bgmusic/edit',
                    del_url: 'music/bgmusic/del',
                    multi_url: 'music/bgmusic/multi',
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
                        {field: 'title', title:"音乐标题",formatter: Controller.api.formatter.short_desc},
                        {field: 'type_id', title:"音乐分类ID"},
                        {field: 'video_cover', title:"封面图",formatter: Controller.api.formatter.thumb},
                        {field: 'music_duration', title:"音乐时长",sortable: true},
                        {field: 'create_time', title:"添加時間",sortable: true},
                        {field: 'music_singer', title:"歌手"},
                        {field: 'order', title:"排序",sortable: true},
                        {field: 'id', title: __('按钮'), table: table, buttons: [
                            {name: 'detail', text: '播放', title: '播放', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'music/bgmusic/play'},
                        ], operate:false, formatter: Table.api.formatter.buttons},
                        {field: 'status', title: __("Status"), formatter: Table.api.formatter.status},
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
                    /*if (type == 'music') {
                        $("#expand").html('');
                    } else {
                        $("#expand").html('<div class="form-group"><label for="c-f_number" class="control-label col-xs-12 col-sm-2">总集数:</label><div class="col-xs-12 col-sm-8"> <div class="input-group margin-bottom-sm"><input id="c-f_number" data-rule="required" class="form-control" name="row[f_number]" placeholder="总集数" type="number" value="0"> <span class="input-group-addon"><i class="fa fa-eye text-success"></i></span></div></div> </div>');
                    }*/
                });

                // $(document).on("click", ".fieldlist .append", function () {
                //     var rel = parseInt($(this).closest("dl").attr("rel")) + 1;
                //     $(this).closest("dl").attr("rel", rel);
                //     $('<dd><input type="text" name="field[' + rel + ']" class="form-control" id="field-' + rel + '" value="" size="10" /> <input type="text" name="value[' + rel + ']" class="form-control" id="value-' + rel + '" value="" size="40" /> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span></dd>').insertBefore($(this).parent());
                // });
                // $(document).on("click", ".fieldlist dd .btn-remove", function () {
                //     $(this).parent().remove();
                // });
                //
                // //拖拽排序
                // require(['dragsort'], function () {
                //     //绑定拖动排序
                //     $("dl.fieldlist").dragsort({
                //         itemSelector: 'dd',
                //         dragSelector: ".btn-dragsort",
                //         dragEnd: function () {
                //
                //         },
                //         placeHolderTemplate: "<dd></dd>"
                //     });
                // });

            },
            formatter: {
                thumb: function (value, row, index) {
                        return '<a href="' + row.music_cover + '" target="_blank"><img src="' + row.music_cover+'" alt="" style="max-height:90px;max-width:120px"></a>';
                   
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
                
            }
        }
    };
    return Controller;
});