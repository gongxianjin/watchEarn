define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'examine/cashall/index',
                    add_url: 'examine/cashall/add',
                    edit_url: 'examine/cashall/edit',
                    del_url: 'examine/cashall/del',
                    multi_url: 'examine/cashall/multi',
                  
                }
            });
            var table = $("#cashall");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                columns: [
                    [
                        {field: '', checkbox: true},
                        {field: 'id', title:"ID",sortable: true},
                        {field: 'order_number', title:"订单编号"},
                        {field: 'amount', title:"申请金额"},
                        {field: 'create_time', title:"申请时间",formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                        {field: 'paypal_mail', title:"paypal_mail"},
                        {field: 'nickname', title:"用户昵称",sortable: true},
                         {field: 'examine_status', title:"是否审核", searchList: {'1':'未审核', '2':'审核通过','3':'审核不通过','4':'驳回'},sortable: true,formatter: Controller.api.formatter.examine_status},
                        {field: 'state', title:"提现状态",searchList: {'0':'待审核', '1':'提现成功','2':'提现失败'},sortable: true,formatter: Controller.api.formatter.state},
                        {field: 'examine_time', title:"审核时间",formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                        {field: 'reason', title:"未通过原因",sortable: true},
                        {field: 'details_url',title:"收入详情",operate: false,formatter: Controller.api.formatter.url},
                        
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
                },
                examine_status:function(value,row,index){
                    if(value == 1){
                        return '<span class="label label-info">待审核</span>';
                    }else if(value == 2){
                        return '<span class="label btn-success">通过</span>';
                    }else if(value == 3){
                        return '<span class="label label-danger">不通过</span>';
                    }else if(value == 4){
                        return '<span class="label label-primary">驳回</span>';
                    }else{
                        return '未定义';
                    }
                },
                state:function(value,row,index){
                    if(value == 0){
                        return '<span class="label label-info">待审核</span>';
                    }else if(value == 1){
                        return '<span class="label btn-success">提现成功</span>';
                    }else if(value == 2){
                        return '<span class="label label-danger">提现失败</span>';
                    }else{
                        return '未定义';
                    }
                },
                

            }
        }
    };
    return Controller;
});