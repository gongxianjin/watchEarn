<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

//数组优良展示的打印方法
if (!function_exists('p')) {
    function p($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}

//json优良展示的打印方法
if (!function_exists('pjson')) {
    function pjson($data)
    {
        header("Content-type: application/json; charset=utf-8", true);
        echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
}


//统一输出格式话的json数据
if (!function_exists('out')) {
    function out($data = [], $code = '200', $msg = 'success')
    {
        $out = array('code' => $code, 'msg' => $msg, 'data' => $data);
        return json($out);
    }
}

//生成唯一的token字符串
if (!function_exists('generate_token')) {
    function generate_token($type = 'md5')
    {
        if ($type == 'sha1'){
            $token = sha1(uniqid().rand(-100000, 100000));
        }
        else {
            $token = md5(uniqid().rand(-100000, 100000));
        }
        return $token;
    }
}

/*
 * 公钥加密
 */
if (!function_exists('public_key_encrypt')) {
    function public_key_encrypt($data)
    {
        $public_key = config('rsa_key_pub');
        $pu_key = openssl_pkey_get_public($public_key);
        openssl_public_encrypt($data, $encryptData, $pu_key);
        $encryptData = base64_encode($encryptData);
        return $encryptData;
    }
}

/*
 * 私钥解密
 * 当密文错误时，返回的是nul, 正确时就是返回的解析出来的明文，一般规定的交互格式是json
 */
if (!function_exists('private_key_decrypt')) {
    function private_key_decrypt($encryptData)
    {
        $private_key = config('rsa_key');
        $pi_key = openssl_pkey_get_private($private_key);
        $encryptData = base64_decode($encryptData);
        openssl_private_decrypt($encryptData, $data, $pi_key);
        return $data;
    }
}

//获取随机字符串
if (!function_exists('get_random_string')) {
    function get_random_string($len = 6, $pre = '', $suf = '')
    {
        $str = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
        $str = str_shuffle($str);
        $max = strlen($str) - (int)$len - 3;
        $start = mt_rand(0, $max);
        $str = substr(str_shuffle($str), $start, $len);
        return $pre.$str.$suf;
    }
}

//生成随机字符传
function uuid()
{
    $uniqid = md5(uniqid(microtime(true),true));
    return $uniqid;
}

//生成订单号
if (!function_exists('build_order_sn')) {
    function build_order_sn()
    {
        $sn = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $pos = rand(8, 12);
        $order_sn = substr_replace($sn, rand(1000, 9999), $pos, 0);
        return $order_sn;
    }
}

/*
 * 随机生成验证码
 */
if (!function_exists('generate_code')) {
    function generate_code($length = 6)
    {
        $min = pow(10, ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }
}

/*
 * 生成数组
 */
if (!function_exists('to_array')) {
    function to_array($data)
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $data = json_decode($data, true);
        return $data;
    }
}


/**
 * 加密函数
 * @param string $txt 需加密的字符串
 * @param string $key 加密密钥，默认读取cookie_auth_key配置
 * @return string 加密后的字符串
 */
function jiami($txt, $key = null)
{
    empty($key) && $key = config('cookie_auth_key');
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
    $nh = rand(0, 64);
    $ch = $chars[$nh];
    $mdKey = md5($key . $ch);
    $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
    $txt = base64_encode($txt);
    $tmp = '';
    $k = 0;
    for ($i = 0; $i < strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = ($nh + strpos($chars, $txt [$i]) + ord($mdKey[$k++])) % 64;
        $tmp .= $chars[$j];
    }
    return $ch . $tmp;
}

/**
 * 解密函数
 * @param string $txt 待解密的字符串
 * @param string $key 解密密钥，默认读取cookie_auth_key配置
 * @return string 解密后的字符串
 */
function jiemi($txt, $key = null)
{
    empty($key) && $key = config('cookie_auth_key');
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
    $ch = $txt[0];
    $nh = strpos($chars, $ch);
    $mdKey = md5($key . $ch);
    $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
    $txt = substr($txt, 1);
    $tmp = '';
    $k = 0;
    for ($i = 0; $i < strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = strpos($chars, $txt[$i]) - $nh - ord($mdKey[$k++]);
        while ($j < 0) {
            $j += 64;
        }
        $tmp .= $chars[$j];
    }
    return base64_decode($tmp);
}
/**
 * 
 * @param  模型，引用传递
 * @param  查询条件
 * @param int  每页查询条数
 * @return 返回
 */
function isMobile($mobile) {
    if (!is_numeric($mobile)) {
        return false;
    }
    if(preg_match("/^1[0123456789]{1}\d{9}$/",$mobile)){
            return true;
    }else{
            return false;
    }
 }

function parsekey($type){
    return str_replace(array('login','reg','findpwd','paypwd','admin'),array(1,2,3,4,5),$type);
}

/**
 *  保存小数点后几位[默认两位小数]
 *  默认为四舍五入,可做修改
 * @author  sjm
 * @param   $v
 * @param   $bit
 */
if (!function_exists('numBit')) {

    function numBit($v = '', $bit = 2)
    {
        if (empty($v) && !isset($v))
            return 0.00;
        return number_format($v, $bit, '.', '');
    }
}

/**
 * curl get请求
 * @param $url  请求地址
 * @param array $header  头部信息
 * @return mixed   返回数据
 */

if (!function_exists('curl_get_https'))
{
    function curl_get_https($url,$header=[]){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);

        //设置一个你的浏览器agent的header
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
        return $tmpInfo;    //返回json对象
    }
}

//模拟手机请求
function curl_agent_https($url,$header=[])
{
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url);

    //设置一个你的浏览器agent的header
    curl_setopt($curl,CURLOPT_PROXY,"172.96.244.17");
    curl_setopt($curl,CURLOPT_PROXYPORT,8703);
    curl_setopt($curl,CURLOPT_PROXYPASSWORD,"ty367c");


    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
    curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 2 );
    $tmpInfo = curl_exec($curl);     //返回api的json对象
    //关闭URL请求
    curl_close($curl);
    return $tmpInfo;    //返回json对象
}

/**
 * 模拟post进行url请求
 * @param string $url
 * @param string $data
 * @param  bool
 */
function curl_json_post($url = '', $data = '', $json = false)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if(!empty($data)){
        if ($json && is_array($data)) {
            $data = json_encode($data);
        }
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 6);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    if($json){
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER,array(
            'Content-Type: application/json;',
            'Content-Length:' . strlen($data)
        ));
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($curl);
    if(curl_errno($curl)){
        $res = curl_errno($curl);
    }
    curl_close($curl);

    return $res;
}

/**
 * 去除字符串 emoj 表情
 *
 * @param $text
 * @return null|string|string[]
 */
function removeEmoji($text) {
    $clean_text = "";
    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $text);
    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);
    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);
    // Match Miscellaneous Symbols
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regexMisc, '', $clean_text);
    // Match Dingbats
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    return $clean_text;
}



if (!function_exists('short_durl')) {
    function short_durl($url)
        {
          $url = "http://api.t.sina.com.cn/short_url/shorten.json?source=1681459862&url_long=".urlencode($url);
          $ip=rand(0,255).'.'.rand(0,255).'.'.rand(0,255).'.'.rand(0,255);
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_HEADER, 0);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-FORWARDED-FOR: {$ip}", "CLIENT-IP: {$ip}"));
          $output = curl_exec($ch);
          curl_close($ch);
         
          $to_domain = json_decode($output, true)[0]['url_short'];
          return $to_domain;
        }
}


    function secToTime($times){   
            try {
                if ($times>0) {  
                    $hour = floor($times/3600);  
                    $minute = floor(($times-3600 * $hour)/60);  
                    $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);
                    if($hour<10){
                        $hour = "0".$hour;
                    }
                    if($minute<10){
                        $minute = "0".$minute;
                    }
                    if($second<10){
                        $second = "0".$second;
                    }
                    $result = $hour.':'.$minute.':'.$second;  
                }  
            } catch (Exception $e) {
                $result = '00:00:00';  
            }
            return $result;  
    }

    /**
     * 检查地址是否已http开始
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    function ckeckHttp($url){
            $pre = "/^((http|https):\/\/)/";
            try {
                 if(preg_match($pre,$url) == 0){
                    return false;
                }else{
                    return true;
                }
            } catch (\Exception $e) {
               
            }
            return false;
           
           
    }
    /**
     * 作用
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    function getrand(){
        return rand(100000,1000000);
    }

    /**
     * 字符串转换成数字
     *
     * @param $string
     * @return string
     */
    function strToHashInt($str)
    {
        $stringHash = substr(md5($str), 8, 4);
        $numStr = base_convert($stringHash, 36, 10);

        return $numStr;
    }
