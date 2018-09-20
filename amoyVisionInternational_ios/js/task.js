setupWebViewJavascriptBridge(function(bridge) {
    bridge.callHandler('login', {'key':'value'}, function responseCallback(responseData) {
        dataParam = JSON.parse(responseData);
        var params = '';
        var data = dataParam;
        for (key in data) {
            params += key + '=' + data[key] + '&';
        }
        var pam = params.substr(0, params.length - 1);
        dataParam = pam;
var app = new Vue({
    el:'#app',
    data:function(){
        return{
            login_flag:"",
            pushList:[],//推送消息列表
            isClick:'',//宝箱是否可点击
            day7:[],//7天数据列表
            total_gold:'0',//金币总数
            islogin:'',//今天是否签到0未签到1,签到
            dayGold:'',//签到可得金币数
            newList:[],//新手任务列表
            isOpenBox: '',//宝箱是否开启0,未开启，1已开启
            tms:"",//倒计时时间戳
            content:"",
            boxGold:'',//打开宝箱金币
            signGold:'',//签到打开金币
            twoRandom:[],//随机显示2个
            ArrList:[],//随机所有数据
        }
    },
    created:function(){
        this.pushUsersAction();
        var _this = this;
        _this.$nextTick(function () {
            _this.slideUp();
        })
    },
    methods: {
        //推送收徒动态信息接口
        pushUsersAction: function () {
            var _this = this;
            instance.post(global.url + 'share/pushUsersAction', pam)
                .then(function (res) {
                    var data = res.data.data;
                    _this.pushList = data;
                    _this.swiper();
                    _this.getData();
                })
        },
        //滑动调用
        swiper: function () {
            this.$nextTick(function () {
                var mySwiper = new Swiper('.swiper-container', {
                    pagination: '.swiper-pagination',
                    direction : 'vertical',
                    loop: true,
                    speed: 400,
                    autoplay: 2000,
                    touchRatio: 0.5,
                    autoplayDisableOnInteraction: false,
                    mode: 'horizontal',
                    freeMode: false,
                    touchRatio: 0.5,
                    longSwipesRatio: 0.1,
                    threshold: 50,
                    followFinger: false,
                    observer: true,//修改swiper自己或子元素时，自动初始化swiper
                    observeParents: true,//修改swiper的父元素时，自动初始化swiper
                })
            })
        },
        //任务大厅数据列表
        getData:function(){
            var _this = this;
            instance.post(global.url+"task/index",pam)
                .then(function(res){
                    if(res.data.code==200){
                        _this.login_flag = res.data.data.login_flag;
                        _this.day7 = res.data.data.sign.day7;
                        _this.total_gold = res.data.data.total_gold;
                        _this.islogin = res.data.data.sign.islogin;
                        _this.dayGold = res.data.data.sign.dayGold;
                        _this.newList = res.data.data.data.menu2;
                        _this.isOpenBox = res.data.data.chest.is;
                        // _this.isOpenBox = 0;
                        _this.tms = res.data.data.chest.time_difference;
                        _this.countdowm(_this.tms);//倒计时

                        //点击收缩事件
                        _this.$nextTick(function () {
                            _this.slideUp();
                        })

                    }
                })
        },
        slideUp:function(){
            $(" .task-new-list").each(function(i,v){
                var isSlideUp = true;
                $(v).find(".task-item-title").click(function(){
                    if(isSlideUp){
                        $(v).find(".task-item-detail").show();
                        $(v).find(".slide").addClass("rotate");
                        var top = $(window).scrollTop();
                        top = $(window).scrollTop() +68;
                        $(window).scrollTop(top);
                        isSlideUp = false;
                    }else{
                        $(v).find(".task-item-detail").hide();
                        $(v).find(".slide").removeClass("rotate");
                        isSlideUp = true;
                    }
                });
            });
        },
        //打开宝箱弹层
        open:function () {
            var _this = this;
            $.post(global.url+"mission_new/handler?id=23",dataParam,function(res){
                gotoLogin(res);//去登录
                if(res.code == 200){
                    _this.total_gold = _this.total_gold+res.data.gold_tribute;
                    _this.tms=res.data.time_difference;
                    _this.countdowm(_this.tms);//倒计时
                    _this.boxGold = res.data.gold_tribute;
                    $("#box-popup").show();
                    //已经开启宝箱
                    _this.isOpenBox=1;
                    //点击收缩事件
                    _this.$nextTick(function () {
                        _this.slideUp();
                    })
                }else{
                    layer.open({
                        content: res.code
                        ,skin: 'msg'
                        ,time: 2 //2秒后自动关闭
                    });
                }
            })
        },
        closeBox:function () {
            $("#box-popup").hide();
        },
        //分享到Twitter收徒
        share_moment:function () {
            $.post(global.url+'Activateshare/share',dataParam,function(res){
                if(res.code == 200){
                    var share_data = res.data;
                    var data = {type:1,title:share_data.default.title,
                        content:share_data.default.content,url:share_data.default.url,
                        imgUrl:share_data.default.imgUrl,imgArray:share_data.default.imgArray,wechatShareType:4};
                    window.WebViewJavascriptBridge.callHandler('shareToOne',data, function(responseData) {
                        var  responseData =  JSON.parse(responseData);
                        if(responseData.code==200){
                            $.post(global.url+"mission_new/handler?id=8",dataParam,function(res){
                                if(res.code == 200 && res.data.is_add_gold > 0){
                                    //调用金币动画效果
                                    personal.goldWelcome('{"count":'+res.data.gold+'}');
                                    $("#box-popup").hide();
                                }
                            })
                        }
                    });
                }
            })
        },
        //倒计时
        countdowm:function(timestamp){
            var self = this;
            var t = self.tms;
            var timer = setInterval(function(){
                t--;
                if(t>0){
                    var days = Math.floor(t / (1 * 60 * 60 * 24));
                    var hours = Math.floor(t / (1 * 60 * 60)) % 24;
                    var minutes = Math.floor(t / (1 * 60)) % 60;
                    var seconds = Math.floor(t / 1) % 60;
                    if (days < 0) days = 0;
                    if (hours < 0) hours = 0;
                    if (minutes < 0) minutes = 0;
                    if (seconds < 0) seconds = 0;
                    var format = '';
                    if(hours<10){
                        var time= "0"+hours + ":" + minutes + ":" +seconds;
                        if(minutes<10){
                            var time= "0"+hours + ":" +"0" + minutes + ":" +seconds;
                            if(seconds<10){
                                var time= "0"+hours + ":" +"0" + minutes + ":" +"0"+seconds;
                            }
                        }
                        self.content = time;
                        //$("#countDown1").text(time);
                    }else{
                        var time= hours + ":" +minutes + ":" + seconds;
                        //$("#countDown1").text(time);
                    }
                }else{
                    clearInterval(timer);
                    self.isOpenBox=0;
                }
            },1000);
        },
        //打开签到弹框
        openSignModel:function(){
            var _this = this;
            instance.post(global.url+"mission_new/handler?id=22",pam)
                .then(function(res){
                    if(res.data.code==200){
                        _this.getData();
                        _this.total_gold = _this.total_gold+res.data.data.gold_flag;
                        _this.islogin = 1;
                        _this.signGold = res.data.data.gold_flag;
                        personal.goldWelcome('{"count":'+res.data.data.gold_flag+'}');

                        //点击收缩事件
                        _this.$nextTick(function () {
                            _this.slideUp();
                        })

                    }else{
                        layer.open({
                            content: res.data.msg
                            ,skin: 'msg'
                            ,time: 2 //2秒后自动关闭
                        });
                    }

                })
        },
        num2date:function(index){
            var num = index%10;
            switch (num){
                case 1:
                    return 'st';
                case 2:
                    return 'nd';
                case 3:
                    return 'rd';
                default:
                    return 'th';
            }
        },
        //调用app方法
        newgoApp:function(index){
            var url = $(index.target).attr("data-src");
            if(url=="openVideo"){
                bridge.callHandler('openIntroduceVideo', {'key':'value'}, function responseCallback(responseData) {})
            }else{
                var _this = this;
                var is_login = $(".app").attr("is_login");
                if(is_login==1&&_this.login_flag==false){
                    $(".registered").show();
                    return false;
                }
                bridge.callHandler('wechatLogin', {'key':'value'}, function responseCallback(responseData) {
                    var  responseData =  JSON.parse(responseData);
                    //数据请求
                    if(responseData.code==200){
                        dataParam.nickname = responseData.nickName;
                        dataParam.headimg = responseData.headImg;
                        dataParam.twitter_id = responseData.twitter_id;
                        /* dataParam.sex = responseData.sex;
                         dataParam.fb_access_token = responseData.fb_access_token;*/
                        //debug(dataParam);
                        $.post(global.url+'mission_new/handler?id=16',dataParam,function(res){
                            if(res.code==200){
                                //金币添加动画调用
                                personal.goldWelcome('{"count":'+res.data.amount+'}');
                                $("a[data-src='wechatLogin']").closest(".task-detail").remove();
                            }else{
                                layer.open({
                                    content: res.msg
                                    ,skin: 'msg'
                                    ,time: 2 //2秒后自动关闭
                                });
                            }
                        })
                    }
                });
            }
        }
    }
})

})});

