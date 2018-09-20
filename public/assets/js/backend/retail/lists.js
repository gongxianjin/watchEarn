define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'retail/lists/index',
                    edit_url: 'retail/lists/edit',
                    del_url: 'retail/lists/del',
                    table: 'retail',
                }
            });
            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                columns: [
                    [
                        {field: '', checkbox: true},
                        {field: 'id', title:"ID",sortable: true,operate:false},
                        {field: 'user_id', title:"用户ID",sortable: true},
                        {field: 'facebook', title:"facebook",operate: 'LIKE %...%'},
                        {field: 'name', title:"用户名字",operate: 'LIKE %...%'},
                        {field: 'real_id', title:"身份证ID",operate: 'LIKE %...%'},
                        {field: 'paypal', title:"PayPal账号",operate: 'LIKE %...%'},
                        {field: 'apply_pay', title:"apply Pay账号",perate: 'LIKE %...%'},
                        {field: 'create_time', title:"申请时间",formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                        {field: 'nationality', title:"国籍",sortable: false,perate: 'LIKE %...%'},
                        {field: 'city', title:"城市",sortable: false,perate: 'LIKE %...%'},
                        {field: 'address', title:"详细地址",sortable: false,perate: 'LIKE %...%'},
                        {field: 'mobile', title:"手机号码",sortable: false,perate: 'LIKE %...%'},
                        {field: 'status', title:"申请状态",sortable:true,searchList: {'1':'申请中', '2':'通过','3':"拒绝通过"},formatter:Controller.api.formatter.f_hot},
                        {field: 'operate', title: __('Operate'), table: table, buttons: [
                               
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
                    if(value == 1){
                        return '<span class="label label-info">提交申请</span>';

                    }else if(value == 2){
                        return '<span class="label label-success">申请通过</span>';
                    }else{
                        return '<span class="label label-danger">拒绝申请</span>';
                    }
                }
            }
        }
    };
    return Controller;
});