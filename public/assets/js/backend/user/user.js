define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'c_user_id',
                columns: [
                    [
                        {field: '', checkbox: true},
                        // {field: 'id', title:"ID",sortable: true,operate:false},
                        {field: 'c_user_id', title:"用户ID",sortable: true},
                        {field: 'nickname', title:"昵称",operate: 'LIKE %...%'},
                        // {field: 'headimg', title:"头像",formatter:Table.api.formatter.image,operate:false},
                        {field: 'mail', title:"邮箱地址",operate: 'LIKE %...%'},
                        {field: 'user_ip', title:"用户IP",operate: 'LIKE %...%'},
                        {field: 'paypal_mail', title:"PayPal账号",operate: 'LIKE %...%'},
                        {field: 'create_time', title:"注册時間",formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                        {field: 'last_login_time', title:"最近登录时间",formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                        {field: 'total_gold_flag', title:"总金币",sortable: true,operate:false},
                       // {field: 'frozen_gold_flag', title:"冻结金币",sortable: true},
                        {field: 'balance', title:"当前金额",sortable: true},
                        {field: 'total_balance', title:"总金额",sortable: true,},
                        {field: 'frozen_balance', title:"冻结金额",sortable: true},
                        {field: 'meid', title:"手机MEID",},
                        {field: 'status', title:"状态",sortable:true,searchList: {'1':'正常', '2':'禁用'},formatter:Controller.api.formatter.f_hot},
                        {field: 'is_cross_read_level', title:"封锁情况",sortable:true,searchList: {'0':'正常', '1':'已封'}, formatter: Controller.api.formatter.is_cross_read_level},
                         {field: 'operate', title: __('Operate'), table: table, buttons: [
                                {name: 'detail', text: '徒弟', title: '徒弟信息', icon: 'fa fa-list', classname: 'label bg-green btn-primary btn-dialog', url: 'user/apprentice/index'},
                                {name: 'detail', text: '收入', title: '收入详情', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'user/profit/index'}
                               
                            ], events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                    if(value == 2){
                     return '<span class="label label-danger">禁用</span>';
                    }else{
                        return '<span class="label label-info">正常</span>';
                    }
                },
                is_cross_read_level: function (value, row, index) { 
                     if(value == 0){
                        return '<span class="label label-info">正常</span>';
                    }else{
                        return '<span class="label  label-danger">已封</span>';
                    }
                }
            }
        }
    };
    return Controller;
});