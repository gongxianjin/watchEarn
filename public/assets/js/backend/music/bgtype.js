define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'music/bgtype/index',
                    add_url: 'music/bgtype/add',
                    edit_url: 'music/bgtype/edit',
                    del_url: 'music/bgtype/del',
                    multi_url: 'music/bgtype/multi',
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
                        {field: 'type_cover', title:"封面图",formatter: Controller.api.formatter.thumb},
                        {field: 'name', title:"分类名称"},
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

                // $(document).on("click", ".fieldlist .append", function () {
                //     var rel = parseInt($(this).closest("dl").attr("rel")) + 1;
                //     $(this).closest("dl").attr("rel", rel);
                //     $('<dd><input type="text" name="field[' + rel + ']" class="form-control" id="field-' + rel + '" value="" size="10" /> <input type="text" name="value[' + rel + ']" class="form-control" id="value-' + rel + '" value="" size="40" /> <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span></dd>').insertBefore($(this).parent());
                // });

                // $(document).on("click", ".fieldlist dd .btn-remove", function () {
                //     $(this).parent().remove();
                // });

                //拖拽排序
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

                }

            }
        }
    };
    return Controller;
});