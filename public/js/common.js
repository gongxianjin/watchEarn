	var global= global || {};	
	//global.url = 'http://www.991yue.com/app/';   // 测试服
    global.url = 'https://tg.199ho.com/app/';   // 正式服
	function GetBaseMsg(){
		 var data = personal.login();
       // var data = '{"ticket":"sHp2r312otmCpnGns3mCqJhovNiBiofbr-CgpL-1ncywjYOlioamlY62gWTAnn6bhKOvlH6ge5euvYaitaWDzq2jra6KZM3XjZSKo7-Ik2ubfrCUi4RzoQ","os":"ios","sign":"sasdvds","meid":"555"}';
        data = JSON.parse(data);
	    var dataParam={'ticket':data.ticket ,'sign': data.sign,"os":data.os,"meid":data.meid};
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

function loading(){
	var html='';
	html+='<div class="main"><div class="loadEffect"><span></span><span></span><span></span>'
		+' <span></span> <span></span><span></span><span></span><span></span></div></div>'
	var load = $("body").append(html);
	return load;
}









