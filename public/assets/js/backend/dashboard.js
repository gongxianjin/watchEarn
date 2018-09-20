define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template','form'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template,Form) {
    Form.events.datetimepicker($("form[role=form]"));
    if ($("#countform .datetimeranges").size() > 0) {
            var ranges = {};
            ranges[__('Today')] = [Moment().startOf('day'), Moment().endOf('day')];
            ranges[__('Yesterday')] = [Moment().subtract(1, 'days').startOf('day'), Moment().subtract(1, 'days').endOf('day')];
            ranges[__('Last 7 Days')] = [Moment().subtract(6, 'days').startOf('day'), Moment().endOf('day')];
            ranges[__('Last 30 Days')] = [Moment().subtract(29, 'days').startOf('day'), Moment().endOf('day')];
            ranges[__('This Month')] = [Moment().startOf('month'), Moment().endOf('month')];
            ranges[__('Last Month')] = [Moment().subtract(1, 'month').startOf('month'), Moment().subtract(1, 'month').endOf('month')];
            var options = {
                timePicker: false,
                autoUpdateInput: false,
                timePickerSeconds: true,
                timePicker24Hour: true,
                autoApply: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss',
                    customRangeLabel: __("Custom Range"),
                    applyLabel: __("Apply"),
                    cancelLabel: __("Clear"),
                },
                ranges: ranges,
            };
            var callback = function (start, end) {
                $(this.element).val(start.format(options.locale.format) + "/" + end.format(options.locale.format));
            };
            var column, index;
            require(['bootstrap-daterangepicker'], function () {
                $("#countform .datetimeranges").each(function () {
                    $(this).on('apply.daterangepicker', function (ev, picker) {
                        callback.call(picker, picker.startDate, picker.endDate);
                    });
                    $(this).on('cancel.daterangepicker', function (ev, picker) {
                        $(this).val('');
                    });
                    index = $(this).data("index");
                    $(this).daterangepicker($.extend({}, options, "RANGE"|| {}), callback);
                });
            });
        }
    var Controller = {
        apprentice:function(){
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dashboard/apprentice',
                    index_url2: 'dashboard/getapprentice',
                    table: 'table',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'c_user_id',
                search:false,
                columns: [
                    [
                        {field: '', checkbox: true},
                        {field: 'c_user_id', title:"用户id",sortable: true,},
                        {field: 'nickname', title:"昵称", operate:'LIKE %...%'},
                        {field:'create_time', title:"注册时间", formatter: Table.api.formatter.datetime,operate: false},
                        {field:'search_time', title:"查询时间", formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true,showColumns: false,visible: false},
                        /*{field: 'short', title:"状态", formatter: Controller.api.formatter.f_hot, searchList: {'1':"正常", '2':"禁用"}, style: 'min-width:100px;'},*/
                        {field: 'apprentice_count', title:"总徒弟个数",sortable: true,operate: false},
                        {field: 'search_apprentice_count', title:"查询范围徒弟个数",operate: false},
                        {field: 's_apprentice_count', title:"总徒孙个数",operate: false},
                        {field: 'search_s_apprentice_count', title:"查询范围徒孙个数",operate: false},
                        {field: 'details_url', title:"徒弟详情",operate: false,formatter: Controller.api.formatter.url},
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
            $(".commonsearch-table button[type='submit']").click(function(){
                var time = $("#search_time").val();
                $.post($.fn.bootstrapTable.defaults.extend.index_url2,{time:time},function(data){
                    $(".all_apprentice_count").html(data.all_apprentice_count);
                    $(".all_apprentice_ren").html(data.all_apprentice_ren);
                    $(".all_s_apprentice_count").html(data.all_s_apprentice_count);
                    $(".all_s_apprentice_ren").html(data.all_s_apprentice_ren);
                })
            })
        },
        user:function(){
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dashboard/user',
                    table: 'table',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'user_id',
                columns: [
                    [
                        {field: '', checkbox: true},
                        {field: 'user_id', title:"用户id",sortable: true,operate: false,},
                        
                        {field: 'nickname', title:"昵称", operate:'LIKE %...%',operate: false},
                        {field: 'create_time', title:"注册时间",sortable: true,formatter:Table.api.formatter.datetime,operate: false},
                        {field:'hs_gold_product_record.update_time', title:"金币获得时间", formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true,showColumns: false,visible: false},
                        {field: 'now_gold_tribute', title:"今日获得金币数量",sortable: true,operate: false},
                        {field: 'total_gold_flag', title:"总金币数量",sortable: true,operate: false},
                        {field: 'gold_flag', title:"剩余金币数量",sortable: true,operate: false},
                        {field: 'balance', title:"余额",sortable: true,operate: false,operate: false},
                        {field: 'total_balance', title:"获取总金额",sortable: true,operate: false},
                        {field: 'status', title:"状态",sortable:true, formatter: Controller.api.formatter.f_hot,operate: false},
                        {field: 'details_url', title:"收入详情",operate: false,formatter: Controller.api.formatter.url},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


            var myChart = Echarts.init(document.getElementById('echart'), 'walden');
               // 指定图表的配置项和数据
            var option = {
                color: ['#4cabce', '#e5323e','#0000FF','#6495ED'],
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend: {
                    data: ['今日金币(K)','新增用户', '登录用户','临时用户','临时登录']
                },
                toolbox: {
                    show: true,
                    orient: 'vertical',
                    left: 'right',
                    top: 'center',
                    feature: {
                        mark: {show: true},
                        dataView: {show: true, readOnly: false},
                        magicType: {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore: {show: true},
                        saveAsImage: {show: true}
                    }
                },
                calculable: true,
                xAxis: [
                    {
                        type: 'category',
                        axisTick: {show: false},
                        data: []
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                series: [
                    {
                        name: '今日金币(K)',
                        type: 'bar',
                        barGap: 0,
                        label: [],
                        data: []
                    },
                    {
                        name: '新增用户',
                        type: 'bar',
                        barGap: 0,
                        label: [],
                        data: []
                    },
                    {
                        name: '登录用户',
                        type: 'bar',
                        label: [],
                        data: []
                    },
                    {
                        name: '临时用户',
                        type: 'bar',
                        barGap: 0,
                        label: [],
                        data: []
                    },
                     {
                        name: '临时登录',
                        type: 'bar',
                        barGap: 0,
                        label: [],
                        data: []
                    }
                ]
            };
            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
             //动态添加数据，可以通过Ajax获取数据然后填充
            Controller.getcount(myChart,{});
            $(window).resize(function () {
                myChart.resize();
            });
            $("#search-button").click(function(){
                var  time = $("#range_time").val();
                 Controller.getcount(myChart,time,"self");
            });
        },
        getcount:function(chartObj,time="",type=""){
                $.post("dashboard/userreport",{time:time,type:type},function(data){
                    if(data.code == 1){
                            chartObj.setOption({
                                xAxis: {
                                    data: data.time
                                },
                                series: [{
                                    name: "今日金币(K)",
                                    data: data.gold_count
                                },{
                                    name: "新增用户",
                                    data: data.reg_count
                                },{
                                    name: "登录用户",
                                    data: data.login_count
                                },{
                                    name: "临时用户",
                                    data: data.temp_reg_count
                                },{
                                    name: "临时登录",
                                    data: data.temp_login_count
                                }]
                            });
                    }else{
                        alert(data.msg);
                    }
                })
        },
        news:function(){

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dashboard/news',
                    index_url2: 'dashboard/getnews',
                    table: 'table',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'video_id',
                search:false,
                columns: [
                    [
                        {field: '', checkbox: true},
                        {field: 'video_id', title:"新闻　id",operate: false},
                        {field: 'title', title:"标题", operate: false},
                        {field:'viewing_number', title:"观看人数",operate: false},
                        {field:'play_count', title:"观看次数", sortable: true,operate:false},
                        {field:'search_time', title:"自定义时间查询", formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true,showColumns: false,visible: false},
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
            $(".commonsearch-table button[type='submit']").click(function(){
                var time = $("#search_time").val();
                $.post($.fn.bootstrapTable.defaults.extend.index_url2,{time:time},function(data){
                    $(".news_count").html(data.news_count);
                    $(".all_paly_count").html(data.all_paly_count);
                    $(".viewing_number").html(data.viewing_number);
                })
            })
        },
        video:function(){

            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dashboard/video',
                    index_url2: 'dashboard/getvideo',
                    table: 'table',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'video_id',
                search:false,
                columns: [
                    [
                        {field: '', checkbox: true},
                        {field: 'video_id', title:"视频id",operate: false},
                        {field: 'title', title:"标题", operate: false},
                        {field:'viewing_number', title:"观看人数",operate: false},
                        {field:'play_count', title:"观看次数", sortable: true,operate:false},
                        {field:'search_time', title:"自定义时间查询", formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true,showColumns: false,visible: false},
                    ]
                ]
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
            $(".commonsearch-table button[type='submit']").click(function(){
                var time = $("#search_time").val();
                $.post($.fn.bootstrapTable.defaults.extend.index_url2,{time:time},function(data){
                    $(".video_count").html(data.video_count);
                    $(".all_paly_count").html(data.all_paly_count);
                    $(".viewing_number").html(data.viewing_number);
                })
            })
        },
        index: function () {
            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echart'), 'walden');
            var video_echart = Echarts.init(document.getElementById('video_echart'), 'walden');

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ["新增用户", "登录用户"]
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: []
                },
                yAxis: {

                },
                grid: [{
                        left: 'left',
                        top: 'top',
                        right: '10',
                        bottom: 30
                    }],
                series: [{
                        name: "新增用户",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: []
                    },
                    {
                        name:"登录用户",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: []
                    }]
            };
            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);
             // 指定图表的配置项和数据
            var video_option = {
                title: {
                    text: '',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ["播放次数"]
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: Orderdata.column
                },
                yAxis: {

                },
                grid: [{
                        left: 'left',
                        top: 'top',
                        right: '10',
                        bottom: 30
                    }],
                series: [{
                        name: "播放次数",
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderdata.visit_count
                    }]
            };

            video_echart.setOption(video_option);

            //动态添加数据，可以通过Ajax获取数据然后填充
            setInterval(function () {
                var time = (new Date()).toLocaleTimeString().replace(/^\D*/, '');
                $.post("dashboard/getcount",{time:time},function(data){
                        Orderdata.column.push(time);
                        Orderdata.createdata.push(data.login_count);
                        Orderdata.paydata.push(data.reg_count);
                        Orderdata.visit_count.push(data.visit_count);
                        //按自己需求可以取消这个限制
                        if (Orderdata.column.length >= 20) {
                            //移除最开始的一条数据
                            Orderdata.column.shift();
                            Orderdata.paydata.shift();
                            Orderdata.createdata.shift();
                        }
                        myChart.setOption({
                            xAxis: {
                                data: Orderdata.column
                            },
                            series: [{
                                    name: "新增用户",
                                    data: Orderdata.paydata
                                },
                                {
                                    name: "登录用户",
                                    data: Orderdata.createdata
                                }]
                        });
                        video_echart.setOption({
                            xAxis: {
                                data: Orderdata.column
                            },
                            series: [
                                {
                                    name: "播放次数",
                                    data: Orderdata.visit_count
                                }]
                        });
                })
            }, 60000);

            $(window).resize(function () {
                myChart.resize();
            });
        },
        downloadchannel:function () {

            Controller.downloadchannelEchart();

            $("#search-button").click(function(){

                Controller.downloadchannelEchart();

            });

        },
        downloadchannelEchart:function()
        {
            var todayechart = Echarts.init(document.getElementById('today_echart'), 'walden');

            todayechart.setOption({
                tooltip : {
                    trigger: 'axis',
                    axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                        type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis : [
                    {
                        type : 'category',
                        data : []
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ]
            });

            var  time = $("#range_time").val();

            $.post("dashboard/downloadChannel",{time:time},function(data){
                todayechart.setOption(data);
            })

        },
        videoshare:function () {

            Controller.videoShareEchart();

            $("#search-button").click(function(){

                Controller.videoShareEchart();

            });

        },
        videoShareEchart:function()
        {
            var todayechart = Echarts.init(document.getElementById('today_echart'), 'walden');

            todayechart.setOption({
                tooltip : {
                    trigger: 'axis',
                    axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                        type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis : [
                    {
                        type : 'category',
                        data : []
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ]
            });

            var  time = $("#range_time").val();

            $.post("dashboard/videoShare",{time:time},function(data){
                todayechart.setOption(data);
            })

        },
        invitationcode:function () {

            Controller.invitationCodeEchart();

            $("#search-button").click(function(){

                Controller.invitationCodeEchart();

            });

        },
        invitationCodeEchart:function()
        {
            var todayechart = Echarts.init(document.getElementById('today_echart'), 'walden');

            todayechart.setOption({
                tooltip : {
                    trigger: 'axis',
                    axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                        type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis : [
                    {
                        type : 'category',
                        data : []
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ]
            });

            var  time = $("#range_time").val();

            $.post("dashboard/invitationCode",{time:time},function(data){
                todayechart.setOption(data);
            })

        },
        cash:function(){
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dashboard/cash',
                    index_url2:'dashboard/searchcash',
                    table: 'table',
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'user_id',
                searchFormVisible: true,
                search:false,
                columns: [
                    [
                        {field: '', checkbox: true},
                        {field: 'user_id', title:"用户id",sortable: true,operate: false,},
                        {field: 'nickname', title:"昵称", operate:'LIKE %...%',operate: false},
                        {field: 'reg_time', title:"注册时间",sortable: true,formatter:Table.api.formatter.datetime,operate: false},
                        
                        {field: 'create_time', title:"提现时间", formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass:'datetimerange', type: 'datetime',sortable: true,showColumns: false
                        ,visible: true},
                        {field: 'cash_amount', title:"已提现总金额",sortable: true,operate: false,operate: false},
                        // {field: 'onered_status', title:"是否提取1元新手红包",sortable: false,operate: false,operate: false},
                        {field: 'balance', title:"余额",sortable: true,operate: false,operate: false},
                        {field: 'examine_cash_amount', title:"审核中金额",operate: false,operate: false},
                        {field: 'total_balance', title:"获取总金额",sortable: true,operate: false},
                        {field: 'total_gold_flag', title:"总金币数量",sortable: true,operate: false},
                        {field: 'gold_flag', title:"剩余金币数量",sortable: true,operate: false},
                        {field: 'status', title:"用户状态",sortable:true, formatter: Controller.api.formatter.f_hot,operate: false},
                         {field: 'operate', title: __('Operate'), table: table, buttons: [
                                {name: 'detail', text: '详情', title: '详情', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'user/profit/index'}
                            ], events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });
            $(".commonsearch-table button[type='submit']").click(function(){
                    var time = $("#create_time").val();
                   
                    $.post($.fn.bootstrapTable.defaults.extend.index_url2,{time:time},function(data){
                        $("#cash_all_money").html(data.cash_all_money+"元");
                        $("#today_examine_cash_money").html(data.today_examine_cash_money+"元");
                        $("#today_cash_success_money").html(data.today_cash_success_money+"元");
                        $("#today_new_cash_success_money").html(data.today_new_cash_success_money+"元");
                        $("#today_cash_all_money").html(data.today_cash_all_money+"元");
                        $("#all_user_gold_flag").html(data.all_user_gold_flag+"元");
                        $("#all_user_balance").html(data.all_user_balance+"元");

                    })
            })

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                //Form.api.bindevent($("form[role=form]"));

            },
            formatter: {

               
                f_hot: function (value, row, index) {
                    console.log(value);     
                    if(value == 2){
                     return '<span class="label label-danger">禁用</span>';
                    }else{
                        return '<span class="label label-info">正常</span>';
                    }
                },
                url:function(value,row,index){
                  
                     return '<a target="_blank" href='+value+'>详情</a>';
                    
                }
            }
        }
    };


    return Controller;
});