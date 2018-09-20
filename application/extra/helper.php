<?php

/**
 * 格式化打印
 * @param  模型，引用传递
 * @param  查询条件
 * @param int  每页查询条数
 * @return 返回
 */
function pp($data){
	echo "<pre>";
	print_r($data);
}

function timediff($begin_time,$end_time){
        if($begin_time < $end_time){
            $starttime = $begin_time;
            $endtime = $end_time;
        }else{
           return 0;
        }
        //计算天数
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
        //计算小时数
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        //计算分钟数
        $remain = $remain%3600;
        $mins = intval($remain/60);
        //计算秒数
        $secs = $remain%60;
        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        if($days<0 || $days == 0){
            if($hours > 0){
                $days = $hours."小时后";
            }else{
                $days = "0天后";
            }
        }else{
            $days = $days."天后";
        }
        return $days;
}


/**
 * 发送HTTP请求方法，目前只支持CURL发送请求
 * @param  string $url    请求URL
 * @param  array  $param  GET参数数组
 * @param  array  $data   POST的数据，GET请求时该参数无效
 * @param  string $method 请求方法GET/POST
 * @return array          响应数据
 */
function curlhttp($url, $param='', $data = '', $method = 'GET',$header=''){
    $opts = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    );

    /* 根据请求类型设置特定参数 */
    $opts[CURLOPT_URL] = $param?$url . '?' . http_build_query($param):$url;

    if(strtoupper($method) == 'POST'){
        $opts[CURLOPT_POST] = 1;
        $opts[CURLOPT_POSTFIELDS] = $data;
        if(is_string($data)){ //发送JSON数据
            $opts[CURLOPT_HTTPHEADER] = array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data),
            );

        }
    }
    if($header){
        $opts[CURLOPT_HTTPHEADER] = $header;
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    //发生错误，抛出异常
    //if($error) throw new \Exception('请求发生错误：' . $error);

    return  $data;
}