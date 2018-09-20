define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'examine/cash/index',
                    add_url: 'examine/cash/add',
                    edit_url: 'examine/cash/edit',
                    del_url: 'examine/cash/del',
                    multi_url: 'examine/cash/multi',
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
                        {field: 'order_number', title:"订单编号"},
                        {field: 'amount', title:"申请金额"},
                        {field: 'create_time', title:"申请时间",formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                        {field: 'paypal_mail', title:"paypal_mail"},
                        {field: 'nickname', title:"用户昵称",sortable: true},
                        {field: 'details_url',title:"收入详情",operate: false,formatter: Controller.api.formatter.url},
                        {field: 'details', title:"收源统计", table: table, buttons: [
                                {name: 'detail', text: '徒孙', title: '徒孙统计', icon: 'fa fa-list', classname: 'label bg-green btn-primary btn-dialog', url: 'user/apprentice/distinctsum'},
                                {name: 'detail', text: '收入', title: '最近一周收入统计', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'user/profit/distinctsum'}
                            ],operate:false, formatter: Table.api.formatter.buttons},
                        {field: 'id', title: __('按钮'), table: table, buttons: [
                            {name: 'detail', text: '是否通过', title: '是否通过', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'examine/cash/examine',callback:function(data){}},
                                   
                        ], operate:false, formatter: Table.api.formatter.buttons}
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
        examine:function(){
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                url: function (value, row, index) {
                    return '<a href="' + value + '" target="_blank" class="label bg-green">收入详情</a>';
                }
            }
        }
    };
    return Controller;
});