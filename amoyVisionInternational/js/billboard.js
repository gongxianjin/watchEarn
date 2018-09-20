$(function () {
    $.post(global.url+'pub/withdrawlist',dataParam,function(res)
    {
        if(res.code == 200){
            var retData = res.data;
            $(".top-1").find('.top-head').find('img').attr('src',retData[0].headimg);
            $(".top-1").find('.top-name').text(retData[0].nickname);
            $(".top-1").find('.top-country img').attr('src',retData[0].country);
            $(".top-1").find('.top-money').text("$" + retData[0].total_balance);

            $(".top-2").find('.top-head').find('img').attr('src',retData[1].headimg);
            $(".top-2").find('.top-name').text(retData[1].nickname);
            $(".top-2").find('.top-country img').attr('src',retData[1].country);
            $(".top-2").find('.top-money').text("$" + retData[1].total_balance);

            $(".top-3").find('.top-head').find('img').attr('src',retData[2].headimg);
            $(".top-3").find('.top-name').text(retData[2].nickname);
            $(".top-3").find('.top-country img').attr('src',retData[2].country);
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
                        '<div class="top-list-country">' +
                        '            <img src="'+v.country +'" style="width: 100%;" alt="">' +
                        '</div>'+
                        '        <div class="top-list-money">' +
                        '$' + v.total_balance +
                        '        </div>' +
                        '        </li>';
                    html += temp;
                }
            });
            $(".lists").append(html);

            topJump(1,50);
            setTimeout(function () {
                topJump(2,80);
            },300);
            setTimeout(function () {
                topJump(3,95);
            },500);
        }

    });

    //统计
    statistics('billboard');
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
    top = top - 12;
    clearInterval();
    if(top > height){
        _this.css('top',top + "px");
        setTimeout(function () {
            topJump(no,height);
        },100);
    }
}

function earn() {
    personal.goEarnPage();
}