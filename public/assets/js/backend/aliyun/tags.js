define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'aliyun/tags/index',
                    add_url: 'aliyun/tags/add',
                    edit_url: 'aliyun/tags/edit',
                    del_url: 'aliyun/tags/del',
                    multi_url: 'aliyun/tags/multi',
                    table: 'tags',
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
                        {field: 'tag', title:"标签", operate:'LIKE %...%'},
                        {field: 'tag_name', title:"标签名", operate: 'LIKE %...%'},
                        {field: 'sort', title:"排序值",formatter: Controller.api.formatter.short_desc,sortable: true},
                        {field: 'use_count', title:"使用次数",sortable: true},
                        {field: 'status', title:"状态",sortable:true, formatter: Controller.api.formatter.f_hot},
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
                f_type:function (value, row, index) {
                       
                    if(value == "film"){
                         return '电影';
                    }else{
                        return '电视剧';
                    }
                },
            }
        }
    };
    return Controller;
});