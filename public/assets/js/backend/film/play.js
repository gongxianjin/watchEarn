define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'adminlte'], function ($, undefined, Backend, Table, Form, Adminlte) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'film/play/index/r_id/'+res_id,
                    add_url: 'film/play/add/r_id/'+res_id,
                    edit_url: 'film/play/edit/r_id/'+res_id,
                    del_url: 'film/play/del/r_id/'+res_id,
                    multi_url: 'film/play/multi/r_id/'+res_id,
                   
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                sortName: 'id',
                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID'},
                        {field: 'r_id', title: "视频ID"},
                        {field: 'title', title: "名称"},
                        {field: 'play_url', title: "播放地址"},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                showExport: false,
                commonSearch: false,
                showToggle: false,
                showColumns: false,
                searchFormVisible: false,
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            /*Form.api.bindevent($("form[role=form]"), function (data) {
                Fast.api.close(data);
            });*/
            Controller.api.bindevent();
        },
        edit: function () {
            //Form.api.bindevent($("form[role=form]"));
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
               
            }
        }
    };
    return Controller;
});