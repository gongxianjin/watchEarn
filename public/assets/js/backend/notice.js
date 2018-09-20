define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            this.initSysconfig();
        }
        //初始化表格
        ,initSysconfig: function () {
            Table.api.init({
                extend: {
                    index_url: '/admin/notice/all',
                    add_url: '/admin/notice/add',
                    edit_url: '/admin/notice/edit',
                    del_url: '/admin/notice/del',
                    table: 'adsource',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                pagination: false,
                search: false,
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: '活动名称'},
                        {field: 'content', title: '活动介绍'},
                        {field: 'url', title: '点击跳转链接'},
                        {field: 'type', title: '通知类型'},
                        {field: 'push_status', title: '推送状态'},
                        {field: 'operate', title: 'Operate', table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

        }
        ,add: function () {
            Controller.api.bindevent();
        }
        ,edit: function () {
            Controller.api.bindevent();
        }
        ,api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});