define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'announce/lists/index',
                    add_url: 'announce/lists/add',
                    edit_url: 'announce/lists/edit',
                    del_url: 'announce/lists/del',
                    multi_url: 'announce/lists/multi',
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
                        {field: 'title', title:"名称"},
                        {field: 'type', title:"类型"},
                        {field: 'content', title:"内容",formatter: Controller.api.formatter.short_desc},
                        {field: 'order', title:"排序",sortable: true},
                        {field: 'status', title: __("Status"), formatter: Table.api.formatter.status},
                        {field: 'create_time', title: "创建时间", formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                $(document).on('click', ".btn-insertlink", function () {
                    var textarea = $("textarea[name='row[value]']");
                    var cursorPos = textarea.prop('selectionStart');
                    var v = textarea.val();
                    var textBefore = v.substring(0, cursorPos);
                    var textAfter = v.substring(cursorPos, v.length);

                    Layer.prompt({title: '请输入显示的文字', formType: 3}, function (text, index) {
                        Layer.close(index);
                        Layer.prompt({title: '请输入跳转的链接URL(包含http)', formType: 3}, function (link, index) {
                            text = text == '' ? link : text;
                            textarea.val(textBefore + '<a href="' + link + '">' + text + '</a>' + textAfter);
                            Layer.close(index);
                        });
                    });
                });
                $("input[name='row[type]']:checked").trigger("click");

                $(document).on("click", ".fieldlistcontent .append", function () {
                    var rel = parseInt($(this).closest("dl").attr("rel")) + 1;
                    var name = $(this).closest("dl").data("name");
                    $(this).closest("dl").attr("rel", rel);
                    $('<dd><input type="text"  name="' + name + '[field][' + rel + ']" class="form-control"   value="" size="10" />  <textarea name="' + name + '[value][' + rel + ']"   name="row[content][value][1]"   cols="60" rows="6" class="form-control editor"></textarea> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> </dd>').insertBefore($(this).parent());
                });

                $(document).on("click", ".fieldlistcontent dd .btn-remove", function () {
                    $(this).parent().remove();
                });

                // 拖拽排序
                // require(['dragsort'], function () {
                //     //绑定拖动排序
                //     $("dl.fieldlist").dragsort({
                //         itemSelector: 'dd',
                //         dragSelector: ".btn-dragsort",
                //         dragEnd: function () {
                //
                //         },
                //         placeHolderTemplate: "<dd></dd>"
                //     });
                // });

            },
            formatter: {
                thumb: function (value, row, index) {
                    return '<a href="' + row.type_cover + '" target="_blank"><img src="' + row.type_cover+'" alt="" style="max-height:90px;max-width:120px"></a>';

                },
                short_desc:function(value,row,index){
                    if(value != null){
                        if(value.length>10){
                            return value.substring(0,10)+"...";
                        }else{
                            return value;
                        }
                    }
                }
            }
        }
    };
    return Controller;
});