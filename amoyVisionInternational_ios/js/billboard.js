$(function () {


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


            $.post(global.url+'pub/withdrawlist',dataParam,function(res) {
                if(res.code == 200){

                    var retData = res.data;
                    $(".top-1").find('.top-head').find('img').attr('src',retData[0].headimg);
                    $(".top-1").find('.top-name').text(retData[0].nickname);
                    $(".top-1").find('.top-money').text("$" + retData[0].total_balance);

                    $(".top-2").find('.top-head').find('img').attr('src',retData[1].headimg);
                    $(".top-2").find('.top-name').text(retData[1].nickname);
                    $(".top-2").find('.top-money').text("$" + retData[1].total_balance);

                    $(".top-3").find('.top-head').find('img').attr('src',retData[2].headimg);
                    $(".top-3").find('.top-name').text(retData[2].nickname);
                    $(".top-3").find('.top-money').text("$" + retData[2].total_balance);

                    var html ="";
                    $.each(retData,function (k,v) {
                        if(k >= 3){
                            var temp = '<li>' +
                                '        <div class="top-list-num">' +
                                (k + 1) +
                                '        </div>' +
                                '        <div class="top-list-head">' +
                                '        <img src="'+ v.headimg +'" style="width: 100%;" alt="">' +
                                '        </div>' +
                                '        <div class="top-list-name">' +
                                v.nickname  +
                                '        </div>' +
                                '        <div class="top-list-money">' +
                                '$' + v.total_balance +
                                '        </div>' +
                                '        </li>';
                            html += temp;
                        }
                    });
                    $(".lists").append(html);

                    topJump(1,10);
                    setTimeout(function () {
                        topJump(2,80);
                    },1000);
                    setTimeout(function () {
                        topJump(3,90);
                    },1500);
                }

            });
        });
    });
});


function topJump(no,height) {
    var _this;
    switch(no)
    {
        case 1:
            _this = $(".top-1");
            break;
        case 2:
            _this = $(".top-2");
            break;
        default:
            _this = $(".top-3");
            break;
    }
    var top = _this.css('top');
    top = parseInt(top);
    top = top - 2;
    clearInterval();
    if(top > height){
        _this.css('top',top + "px");
        setTimeout(function () {
            topJump(no,height);
        },10);
    }
}

function earn() {

    setupWebViewJavascriptBridge(function(bridge) {
        bridge.callHandler('goEarnPage', {}, function responseCallback() {});

    });

    // console.log(1111);
    // personal.goEarnPage();
}