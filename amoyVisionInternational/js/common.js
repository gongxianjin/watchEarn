	var global= global || {};	
	// global.url = 'http://www.hesheng138.com/app/';   // 测试服
     global.url = 'https://tg.199ho.com/app/';   // 正式服
	var version = "";
	function GetBaseMsg(){
		 var data = personal.login();
       // var data = '{"ticket":"sHp2r312otmCpnGns3mCqJhovNiBiofbr-CgpL-1ncywjYOlioamlY62gWTAnn6bhKOvlH6ge5euvYaitaWDzq2jra6KZM3XjZSKo7-Ik2ubfrCUi4RzoQ","os":"ios","sign":"sasdvds","meid":"555"}';
        data = JSON.parse(data);
	    var dataParam={'ticket':data.ticket ,'sign': data.sign,"os":data.os,"meid":data.meid};
	    version = data.version;
	    return dataParam;
   }
	
	function gotoLogin(res){
    		if(res.code == 9999){
    			openLoginPage();
    		}
    }    

	var instance = axios.create({  //axios默认配置
	   baseURL: global.url,
	   headers: {
	      'Content-Type': 'application/x-www-form-urlencoded',
	      'Accept': 'application/json'
	   }
	});
	
	
	var dataParam = GetBaseMsg();
	var params = '';
	var data = dataParam;
	for (key in data){
	  params += key + '=' + data[key] + '&';
	}
	var pam = params.substr(0, params.length - 1);

	
	//pam +='&code_or_phone='+code;//传参
  

	var u = navigator.userAgent;
	var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
	var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端


// 获取浏览器参数
function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

    //点击弹层隐藏
    function hide(className){
    	$(className).click(function(){
    		$(className).hide();
    	});
    }
    
    //阻止事件冒泡   
     function stop(className){
    	$(className).click(function(e){
    		e.stopPropagation();
    	});
    }

    function loading() {
        var html = '';
        html += '<div class="main"><div class="loadEffect"><span></span><span></span><span></span>'
            + ' <span></span> <span></span><span></span><span></span><span></span></div></div>'
        var load = $("body").append(html);
        return load;
    }


    //检测插件版本号是否需要更新
    function checkPlugin(a, b) {
        var a = toNum(a);
        var b = toNum(b);
        if (a == b) {
           //相同
            return 1;
        } else if (a > b) {
            //a > b  a 为新版本
            return 2;
        } else {
            //a < b  b 为新版本
            return 0;
        }
    }

    function toNum(a){
        var a=a.toString();
        var c=a.split('.');
        var num_place=["","0","00","000","0000"],r=num_place.reverse();
        for (var i=0;i<c.length;i++){
            var len=c[i].length;
            c[i]=r[len]+c[i];
        }
        var res= c.join('');
        return res;
    }
    
    //统计访问接口
    function statistics(access)
    {
        dataParam.access = access;
        $.post(global.url + 'pub/count',dataParam,function () {});
    }








