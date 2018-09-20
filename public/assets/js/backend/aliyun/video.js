define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'aliyun/video/index',
                    edit_url: 'aliyun/video/edit',
                    play_url: 'aliyun/video/play',
                    // del_url: 'aliyun/video/del',
                    table: 'user_video',
                }
            });
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                columns: [
                    [
                        {field: '', checkbox: true},
                        // {field: 'id', title:"ID",sortable: true,operate:false},
                        {field: 'id', title:"视频ID",sortable: true},
                        {field: 'title', title:"标题",operate: 'LIKE %...%'},
                        {field: 'aliyun_video_id', title:"阿里云视频ID",operate: 'LIKE %...%'},
                        {field: 'cover_img', title:"缩略图",formatter: Controller.api.formatter.thumb},
                        {field: 'aliyun_video_id', title: '播放', table: table, buttons: [
                                {name: 'aliyun_video_id', text: '  ', title: '播放', icon: 'fa fa-toggle-right', classname: 'btn btn-xs btn-primary btn-dialog', url: 'aliyun/video/play'}
                            ],formatter: Table.api.formatter.buttons,operate: false},
                        {field: 'duration', title:"时长(毫秒)",sortable: true,operate: false},
                        {field: 'joinUser.nickname', title:"作者",operate: 'LIKE %...%'},
                        {field: 'create_time', title:"上传时间",formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                        {field: 'w_h', title:"宽高比",sortable: false,operate:false},
                        {field: 'tag', title:"标签",sortable: false},
                        {field: 'status', title:"状态",sortable:true,searchList: {'1':'审核中', '2':'审核通过',"3":"驳回"},formatter:Controller.api.formatter.f_hot},
                        {field: 'operate', title: __('Operate'), table: table,  events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                f_hot: function (value, row, index) { 
                    if(value == 1){
                     return '<span class="label label-info">审核中</span>';
                    }else if(value == 2){
                        return '<span class="label label-success">通过</span>';
                    }else{
                        return '<span class="label label-danger">驳回</span>';
                    }
                },
                thumb: function (value, row, index) {
                    return '<a href="' + row.cover_img + '" target="_blank"><img src="' + row.cover_img+'" alt="" style="max-height:90px;max-width:120px"></a>';

                }
            }
        }
    };
    return Controller;
});