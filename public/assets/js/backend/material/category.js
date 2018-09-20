define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'material/category/index',
                    add_url: 'material/category/add',
                    edit_url: 'material/category/edit',
                    del_url: 'material/category/del',
                    table: 'material_category',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {field: '', checkbox: true},
                        {field: 'id', title:"ID"},
                        {field: 'name', title:"分类名称"},
                        {field: 'description', title:"分类描述",operate: false, formatter: Controller.api.formatter.url},

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

            }
        }
    };
    return Controller;
});