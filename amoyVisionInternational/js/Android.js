var global = global || {};
var AndroidBridge = false;
var AndroidReady = (function() {
    var funcs = [];
    var ready = false;
    function handler(bridge) {
        if (ready) return;
        for (var i = 0; i < funcs.length; i++) {
            funcs[i].call(bridge)
        }
        ready = true;
        funcs = null
    }
    if (AndroidBridge) {
        handler(AndroidBridge)
    } else {
        document.addEventListener('AndroidReady',
        function() {
            handler(AndroidBridge)
        },
        false)
    }
    return function AndroidReady(fn) {
        if (ready) {
            fn.call(AndroidBridge)
        } else {
            funcs.push(fn)
        }
    }
})();
function connectWebViewJavascriptBridge(callback) {
    if (window.WebViewJavascriptBridge) {
        callback(WebViewJavascriptBridge)
    } else {
        document.addEventListener('WebViewJavascriptBridgeReady',
        function() {
            callback(WebViewJavascriptBridge)
        },
        false)
    }
}
connectWebViewJavascriptBridge(function(bridge) {
        bridge.init(function(message, responseCallback) {
            var data = {
            };
        });
        bridge.registerHandler("functionInJs", function(data, responseCallback) {
            var responseData = "";
            responseCallback(responseData);
        });
        window.AndroidBridge = bridge;
        var event = document.createEvent('HTMLEvents');
        event.initEvent("AndroidReady", true, true);
        event.eventType = 'AndroidReady';
        document.dispatchEvent(event)
});

function OpenNewWebPage(url){
    window.WebViewJavascriptBridge.callHandler('openNewWebPage',url, function(responseData) {});
}
function gotoAPPLogin(){
    //alert(1);
    personal.openLoginPage();
    //window.WebViewJavascriptBridge.callHandler('toMainPage',"", function(responseData) {});
}