define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/apprentice/index/ids/'+ids+'/grandsonids/'+grandsonids+'/meid/'+meid+'/passwd/'+passwd+'/type/'+type+'/ip/'+ip+'/level/'+level+'/leveltype/'+leveltype,
                    edit_url: 'user/user/edit',
                    multi_url: 'user/user/multi',
                    table: 'user'
                }
            });
            var table = $("#apprentice");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'c_user_id',
                columns: [
                     [
                         {field: '', checkbox: true},
                        {field: 'c_user_id', title:"用户ID",sortable: true},
                        {field: 'nickname', title:"昵称",operate: 'LIKE %...%'},
                        {field: 'login_passwd', title:"密码",sortable: true},
                         {field: 'mail', title:"邮箱地址",operate: 'LIKE %...%'},
                        {field: 'paypal_mail', title:"PayPal账号",operate: 'LIKE %...%'},
                        {field: 'create_time', title:"注册時間",formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                        {field: 'last_login_time', title:"最近登录时间",formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                        {field: 'total_gold_flag', title:"总金币",sortable: true,operate:false},
                        {field: 'balance', title:"当前金额",sortable: true},
                        {field: 'meid', title:"手机MEID",sortable: true},
                        {field: 'user_ip', title:"IP地址",sortable: true},
                       
                        {field: 'status', title:"状态",sortable:true, formatter: Controller.api.formatter.f_hot},
                         {field: 'operate', title:"收入详情", table: table, buttons: [
                                {name: 'detail', text: '详情', title: '详情', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'user/profit/index'},
                            ], formatter: Table.api.formatter.operate}
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
                f_hot: function (value, row, index) {
                    console.log(value);     
                    if(value == 2){
                     return '<span class="label label-danger">禁用</span>';
                    }else{
                        return '<span class="label label-info">正常</span>';
                    }
                }
            }
        }
    };
    return Controller;
});