define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/profit/index/ids/'+ids,
                   
                }
            });
            var table = $("#Profit");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
               // sortName: 'id',
               // sortName: 'visit_count',
                columns: [
                    [
                       /* {field: '', checkbox: true},*/
                        {field: 'nickname', title:"昵称",operate: false},
                        {field: 'type_key', title:"任务"},
                        {field: 'gold_tribute', title:"金币数",operate:"=",sortable:true},
                        {field: 'taskname', title:"任务名称",operate: false},
                        {field:'create_time', title:"添加時間", formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true},
                     /**/
                        {field: 'title', title:"描述",sortable: true,operate: false},
                       
                       
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
             
                $(document).on('click', "input[name='row[f_type]']", function () {
                    var type = $(this).val();
                    /*if (type == 'user') {
                        $("#expand").html('');
                    } else {
                        $("#expand").html('<div class="form-group"><label for="c-f_number" class="control-label col-xs-12 col-sm-2">总集数:</label><div class="col-xs-12 col-sm-8"> <div class="input-group margin-bottom-sm"><input id="c-f_number" data-rule="required" class="form-control" name="row[f_number]" placeholder="总集数" type="number" value="0"> <span class="input-group-addon"><i class="fa fa-eye text-success"></i></span></div></div> </div>');
                    }*/
                });

                $(document).on("click", ".fieldlist .append", function () {
                    var rel = parseInt($(this).closest("dl").attr("rel")) + 1;
                    $(this).closest("dl").attr("rel", rel);
                    $('<dd><input type="text" name="field[' + rel + ']" class="form-control" id="field-' + rel + '" value="" size="10" /> <input type="text" name="value[' + rel + ']" class="form-control" id="value-' + rel + '" value="" size="40" /> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span></dd>').insertBefore($(this).parent());
                });
                $(document).on("click", ".fieldlist dd .btn-remove", function () {
                    $(this).parent().remove();
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