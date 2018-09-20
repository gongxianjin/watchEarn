define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ad/adsource/index',
                    add_url: 'ad/adsource/add',
                    edit_url: 'ad/adsource/edit',
                    del_url: 'ad/adsource/del',
                    table: 'adsource',
                }
            });
            var table = $("#source");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {field: '', checkbox: true},
                        {field: 'id', title:"ID"},
                        {field: 'ad_name', title:"广告名称",formatter: Controller.api.formatter.short_desc},
                        {field: 'ad_url', title:"广告地址", formatter: Controller.api.formatter.url},
                     /*   {field: 'title', title:"标题", formatter: Controller.api.formatter.title},
                        {field: 'img', title:"图片"},*/
                        {field: 'status', title: __('Status'), formatter: Controller.api.formatter.f_hot, searchList: {'1': '待发布', '2':'启用','3':'禁用'}, style: 'min-width:100px;'},
                        {field:'create_time', title:"添加時間", formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                        {field: 'operate', title: __('Operate'), table: table, buttons: [], events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
            },
            formatter: {
                url: function (value, row, index) {
                    return '<a href="' + value + '" target="_blank" class="label bg-green">' + value + '</a>';
                },
                short_desc:function(value,row,index){
                    if(value.length>16){
                        return value.substring(0,10)+"...";
                    }else{
                        return value;
                    }
                },
                title:function(value,row,index){
                    return value;
                },
                
                f_hot: function (value, row, index) {
                    console.log(value);     
                    if(value == 3){
                        return '<span class="label label-danger">禁用</span>';
                    }else if(value == 2){
                         return '<span class="label label-success">正常</span>';
                    }else{
                          return '<span class="label label-info">待发布</span>';
                    }
                },
                
            }
        }
    };
    return Controller;
});