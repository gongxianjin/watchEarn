define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'images/images/index',
                    add_url: 'images/images/add',
                    edit_url: 'images/images/edit',
                    del_url: 'images/images/del',
                    table: 'images',
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
                        {field: 'name', title:"图片名称"},
                        {field: 'url', title:"图片地址",operate: false, formatter: Controller.api.formatter.url},
                        {field: 'status', title: __('status'), formatter: Controller.api.formatter.f_hot, searchList: {'0': '正常', '1':'禁用'}, style: 'min-width:100px;'},
                        {field:'create_time', title: __('Create Time'), formatter: Table.api.formatter.datetime, operate: false, addclass:'datetimerange', type: 'datetime',sortable: false},

                        {field: 'operate', title: __('Operate'), table: table,  buttons: [],events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                    return '<a style="width: 100px;height: 100px;background-color:transparent !important;" href="' + value + '" target="_blank" class="label bg-green"><img width="100px" height="50px;" src="'+value+'"></a>';
                },

                f_hot: function (value, row, index) {

                    if(value == 1){
                        return '<span class="label label-danger">禁用</span>';
                    }else if(value == 0){
                        return '<span class="label label-success">正常</span>';
                    }
                },

            }
        }
    };
    return Controller;
});