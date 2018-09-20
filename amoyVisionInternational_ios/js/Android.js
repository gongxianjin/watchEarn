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
function setupWebViewJavascriptBridge(callback) {
        if (window.WebViewJavascriptBridge) { return callback(WebViewJavascriptBridge); }
        if (window.WVJBCallbacks) { return window.WVJBCallbacks.push(callback); }
        window.WVJBCallbacks = [callback];
        var WVJBIframe = document.createElement('iframe');
        WVJBIframe.style.display = 'none';
        WVJBIframe.src = 'https://__bridge_loaded__';
        document.documentElement.appendChild(WVJBIframe);
        setTimeout(function() { document.documentElement.removeChild(WVJBIframe) }, 0)
}
function OpenNewWebPage(url){
    setupWebViewJavascriptBridge(function(bridge) {
        bridge.callHandler('openNewWebPage', {'url':url}, function responseCallback(responseData) {})
    })
}
function gotoAPPLogin(){
    setupWebViewJavascriptBridge(function(bridge) {
        bridge.callHandler('openLoginPage', {'key':'value'}, function responseCallback(responseData) {})
    })
}
