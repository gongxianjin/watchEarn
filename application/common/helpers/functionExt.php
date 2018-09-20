<?php
if (!function_exists('getArrVal')) {

    function getArrVal($data, $key) {
        return isset($data[$key]) ? $data[$key] : '';
    }

}
if (!function_exists('getArrNum')) {

    function getArrNum($data, $key) {
        return isset($data[$key]) ? $data[$key] : '0';
    }

}
if (!function_exists('hoursAtFmart')) {

    function hoursAtFmart($num) {
        $num=(string)$num;
        if(strlen($num)<10){
            $n=10-strlen($num);
            for ($i=0; $i < $n ; $i++) { 
                $num=$num.'0';
            }
        }
        return $num;
    }
}
if (!function_exists('getTimeNum')) {

    function getTimeNum($num) {
        return $num>0?$num:0;
    }
}
if (!function_exists('continuousArrFilter')) {
    function continuousArrFilter($data, $key,$num) {
        return isset($data[$key]) ? $data[$key] : '0';
    }
}

if (!function_exists('telHideFmart')) {
    function telHideFmart($str) {
        return substr($str, 0,3).'*****'.substr($str, 7,4);
    }
}

if (!function_exists('getClientIP')) {
    function getClientIP() {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
            $ip = getenv("REMOTE_ADDR");
        } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "";
        }
        return ($ip);
    }
}





