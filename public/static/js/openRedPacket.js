!
function e(n, o, a) {
	function t(c, r) {
		if (!o[c]) {
			if (!n[c]) {
				var s = "function" == typeof require && require;
				if (!r && s) return s(c, !0);
				if (i) return i(c, !0);
				throw new Error("Cannot find module '" + c + "'")
			}
			var l = o[c] = {
				exports: {}
			};
			n[c][0].call(l.exports, function(e) {
				var o = n[c][1][e];
				return t(o || e)
			}, l, l.exports, e, n, o, a)
		}
		return o[c].exports
	}
	for (var i = "function" == typeof require && require, c = 0; c < a.length; c++) t(a[c]);
	return t
}({
	1: [function(e, n, o) {
		$(function() {
			var e = {
				init: function() {
					this.getDownloadUrl()
				},
				initEvent: function() {
					var n = e;
					switch (n.chName = methods.getUrlParam(location.href, "ch"), n.environment = "" === methods.getUrlParam(location.href, "env") ? "p" : methods.getUrlParam(location.href, "env"), n.userId = n.params.code = methods.getUrlParam(location.href, "uId"), n.environment) {
					case "dev":
						n.host = n.env_pool.d;
						break;
					case "test":
						n.host = n.env_pool.t;
						break;
					case "staging":
						n.host = n.env_pool.s;
						break;
					case "production":
						n.host = n.env_pool.p;
						break;
					default:
						n.host = n.env_pool[n.environment]
					}
					n.remoteLog("pv"), n.getUserInfo(), n.bindEvent(), "o" === n.chName || "bj" === n.chName ? n.uvLand() : "ar" === n.chName && n.uvLucky()
				},
				userId: "",
				chName: "",
				downloadUrl: "http://a.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1364510679446",
				environment: "",
				params: {
					code: "",
					//mobile: ""
				},
				env_pool: {
					d: "http://192.168.100.153:8001/",
					t: "http://114.215.103.173:9140/",
					s: "http://123.56.18.207:8080/",
					p: "http://www.xinwenzhuan.coohua.com/"
				},
				ch_pool: [],
				host: "",
				channel: "",
				os: "",
				getUserInfo: function() {
					var n = e;
					$.ajax({
						type: "get",
						url: n.host + "api/shareInfo",
						data: {
							userId: n.userId
						},
						success: function(e) {
							6666666 != n.userId ? ($("#user-img").attr("src", e.result.avatar_url), $("#user-name").text(e.result.nick_name)) : $(".user-money").css("visibility", "hidden")
						}
					})
				},
				getDownloadUrl: function() {
					var e = this;
					$.ajax({
						url: "http://cms001.oss-cn-beijing.aliyuncs.com/xinwenzhun_share/4y3mpkeng2Z1n7OG.html",
						contentType: "application/x-www-form-urlencoded;charset=UTF-8",
						type: "GET",
						success: function(n) {
							var o = JSON.parse(n.substring(n.indexOf("{"), n.indexOf("<\/script>")));
							console.log(o), e.ch_pool = o.downloadList, e.initEvent()
						}
					})
				},
				uvLand: function() {
					var n = e;
					$.ajax({
						type: "post",
						url: n.host + "wxShare/uvLand",
						data: {
							uId: n.userId
						}
					})
				},
				uvLucky: function() {
					var n = e;
					$.ajax({
						type: "post",
						url: n.host + "wxShare/luckyShareClick",
						data: {
							uId: n.userId
						}
					})
				},
				remoteLog: function(n, o) {
					var a = e,
						t = navigator.userAgent.toLowerCase(),
						i = /iphone|ipad|ipod/.test(t) ? "iOS" : "android";
					a.os = /iphone|ipad|ipod/.test(t) ? "iOS" : "android";
					//var c = a.trim($(".mobile").val(), "g"),
						r = "",
						s = parseInt(2 * Math.random());
					a.chName && a.ch_pool.map(function(e) {
						a.chName === e.ch && (a.channel = r = -1 !== e.name.indexOf("（") ? e.name.substring(0, e.name.indexOf("（")) : a.chName, "android" === i ? 1 == s && e.androidTestB ? a.downloadUrl = e.androidTestB : a.downloadUrl = e.android : 1 == s && e.iOSTestB ? a.downloadUrl = e.iOSTestB : a.downloadUrl = e.iOS)
					}), sa.track("WebShare", {
						element_page: "分享落地页",
						element_name: n,
						page_url: location.href,
						channel: r,
						//phone_number: c,
						userId: "" === a.userId ? null : a.userId,
						ua: t,
						$os: i,
						$device_id: "" === a.userId ? null : a.userId,
						$imei: "iOS" === i ? null : a.userId
					}, function() {
						o && o()
					})
				},
				bindEvent: function() {
					var e = this,
						n = 0,
						o = document.getElementById("model");
					document.getElementsByClassName("howOpen")[0].onclick = function() {
						o.style.display = "block"
					}, document.getElementById("closeModal").onclick = function() {
						o.style.display = "none"
					}, document.getElementById("closeModal2").onclick = function() {
						o.style.display = "none"
					}, $(".btn-chb").on("click", function() {
						$(this).addClass("active"), $(".user-money").css("visibility", "hidden"), setTimeout(function() {
							$(".btn-chb").hide()
						}, 300), setTimeout(function() {
							$(".hb-cover").addClass("n-open-img").css("z-index", "-1"), $("#user-name").text("快来淘视界和我一起赚大钱吧~"), $(".hb-main").css("transform", "translate(0px, -3rem)"), $(".hb-main-other").css("transform", "translate(-0.8rem, 0.5rem) scale(1)"), $(".m-inv-gift").css("opacity", "0").hide(), $(".form-box").css("opacity", "1").show()
						}, 500), $(".hb-cover").addClass("n-open")
					})/*, $(".download").on("click", function() {
						e.remoteLog("download", function() {
							var n = e.host + "invite/bind";
							e.params.mobile = e.trim($(".mobile").val(), "g");
							"" !== e.params.mobile && /^1[34578]\d{9}$/.test(e.params.mobile) ? $.ajax({
								type: "post",
								url: n,
								data: e.params,
								error: function() {
									window.location.href = e.downloadUrl, setTimeout(function() {
										"android" === e.os ? "appshare_wechatmoment" === e.channel ? window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1369766173749" : "ma_wechat" === e.channel ? window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1369766600430" : "ma_wechatmoment" === e.channel ? window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1369766490342" : "ma_erweima" === e.channel ? window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1370260653494" : "ma_qq" === e.channel ? window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1369766774491" : window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1378145629628" : window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1378145629628"
									}, 8e3)
								}
							}) : "" === e.params.mobile ? alert("请输入手机号，就能顺利领取红包啦~") : 11 !== e.params.mobile.length ? alert("请正确输入手机号，就能顺利领取红包啦～") : /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/.test(e.params.mobile) ? (window.location.href = e.downloadUrl, setTimeout(function() {
								"android" === e.os ? "appshare_wechatmoment" === e.channel ? window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1369766173749" : "ma_wechat" === e.channel ? window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1369766600430" : "ma_wechatmoment" === e.channel ? window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1369766490342" : "ma_erweima" === e.channel ? window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1370260653494" : "ma_qq" === e.channel ? window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1369766774491" : window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1378145629628" : window.location.href = "http://a2.app.qq.com/o/simple.jsp?pkgname=com.coohua.xinwenzhuan&ckey=CK1378145629628"
							}, 8e3)) : alert("请正确输入手机号，就能顺利领取红包啦～")
						})
					})*/
				},
				/*trim: function(e, n) {
					var o;
					return o = e.replace(/(^\s+)|(\s+$)/g, ""), "g" === n.toLowerCase() && (o = o.replace(/\s/g, "")), o
				}*/
			};
			e.init()
		})
	}, {}]
}, {}, [1]);