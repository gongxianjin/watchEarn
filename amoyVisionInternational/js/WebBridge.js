/**
 * 原始方法，两个注册都需要调用这个方法才能使用
 * @param callback
 * @returns {*}
 */
function connectWebViewJavascriptBridge(callback) {
    if (window.WebViewJavascriptBridge) {
        return callback(WebViewJavascriptBridge)
    } else {
        document.addEventListener('WebViewJavascriptBridgeReady', function () {
            callback(WebViewJavascriptBridge)
        }, false)
    }
    if (window.WVJBCallbacks) {
        return window.WVJBCallbacks.push(callback);
    }
    window.WVJBCallbacks = [callback];
    var WVJBIframe = document.createElement('iframe');
    WVJBIframe.style.display = 'none';
    WVJBIframe.src = 'https://__BRIDGE_LOADED__';
    document.documentElement.appendChild(WVJBIframe);
    setTimeout(function () {
        document.documentElement.removeChild(WVJBIframe)
    }, 0)
}

/**
 * 初始化
 * @param callback
 * @constructor
 */
window.onload = function () {
    (function WebBridgeInit(callback) {
        connectWebViewJavascriptBridge(function (bridge) {
            bridge.init(function (message, responseCallback) {
                console.log('Javascript 接收从oc发送过来的信息:', message);
                if (responseCallback) {
                    callback && callback();
                }
            });
        });
    })();
};

/**
 * JS给客户端发送消息
 * @param data 要发送的消息
 * data统一格式{func: "", path: "ne://", params: {xx: ""}}
 * @param callback
 */
function bridgeSend(data, callback) {
    connectWebViewJavascriptBridge(function (bridge) {
        bridge.send(data, function responseCallback(responseData) {
            // Javascript 获取响应信息
            callback && callback(responseData);
        });
    });
}

/**
 * 客户端调JS
 * @param callback
 */
function registerHandler(callback) {
    connectWebViewJavascriptBridge(function (bridge) {
        bridge.registerHandler("NENativeCallH5", function (data, responseCallback) {
            callback && callback(data)
        });
    });
}

/**
 * JS调客户端
 * @param data
 * @param callback
 */
function callHandler(data, callback) {
    connectWebViewJavascriptBridge(function (bridge) {
        bridge.callHandler("NEH5CallNative", data, function (response) {
            // response 客户端返回的数据
            callback && callback(response);
            console.log("客户端响应后返回的数据:" + response);
        });
    });
}

