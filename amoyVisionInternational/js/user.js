
var app = new Vue({
    el: "#app",
    data: function data() {
        return {
            login_flag: '', //用户当前登录类型 false 临时用户 true真实注册用户
            gold_flag: '', //用户当前金币数量
            total_balance: '', //	用户总收入
            balance: '', //用户当前余额
            headimg: '../images/default_head.png', //用户头像
            invitation_code: '', //邀请码
            share_code_status: '', //是否输入过邀请码 false 没有 true 已输入过
            isLogin: '',
            gold_tribute: '' ,//红包奖励金币
            nickname:'',//昵称
            is_hidden_first_mission:'',//是否隐藏任务
        };
    },
    created: function created() {
        var _this2 = this;
        var date = new Date();
        date.setTime(date.getTime()+5*60*1000);
        var _this = this;
        instance.post(global.url + 'User/index', pam).then(function (res) {
            var data = res.data.data;
            var data2 = data.userMsg;
            _this.login_flag = data.login_flag;
            _this.isNewRed = data2.redcash_status;
            _this.isOne = data2.ored_status;
            _this.is_bind_wx = data2.is_bind_wx;
            _this.nickname = data2.nickname;
            _this.headimg = decodeURIComponent(data2.headimg).replace(/http:/,"https:"); //用户头像
            _this.gold_flag = data2.gold_flag; //用户当前金币数量
            _this.total_balance = data2.total_balance; //用户总收入
            _this.balance = data2.balance; //用户当前余额
           _this.is_hidden_first_mission  = data2.is_hidden_first_mission;
            if (data.login_flag) {
                //真实注册
                _this.invitation_code = "My invitation code：" + data2.invitation_code; //邀请码
                _this.isLogin = true;
                if (data2.share_code_status) {
                    //是否输入过邀请码 false 没有 true 已输入过
                    _this.share_code_status = false;
                } else {
                    _this.share_code_status = true;
                    $("#yaoqing").click(function () {
                        window.location.href = "enterCode.html";
                    });
                }
            } else {
                //临时用户
                _this.invitation_code = "Login"; //邀请码
                _this.isLogin = false;
                if (data2.share_code_status) {
                    //已输入
                    _this.share_code_status = false; //隐藏
                } else {
                    _this.share_code_status = true; //是否输入过邀请码 false 没有 true 已输入过
                    $("#yaoqing").click(function () {
                        $(".registered1").show();
                    });
                }
            }
        })
    },

    methods: {
        //跳转到Login界面
        goLogin: function goLogin() {
            personal.openLoginPage();
        },
        goTo:function(){
            window.location.href = "1yuan_withdraw.html";
        },
        showRe:function () {
           $(".registered").show();
        }

    }

});

        hide(".registered1");
        stop(".regCon");

        hide(".registered");
        stop(".regCon");

        $(".redPack,.redPack1").click(function () {
            $(this).hide();
        });

        $(".redCon,.redCon1").click(function (e) {
            e.stopPropagation();
        });