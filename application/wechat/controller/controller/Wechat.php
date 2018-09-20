<?php
namespace app\wechat\controller;

use think\Controller;
use think\Request;

class Wechat extends Controller
{
    private $token = "yigexiaoshuo8745";
    private function checkSignature()  
        {  
             //1.接收微信发过来的get请求过来的4个参数   
             $signature = $_GET["signature"];  
             $timestamp = $_GET["timestamp"];  
             $nonce = $_GET["nonce"]; //随机数  
               
             //2.加密  
             //1.将token,timestamp,once 三个参数进行字典序排序  
             $tmpArr = array($this->token,$timestamp,$nonce);  
             sort($tmpArr,SORT_STRING);  
               
             //2.将三个参数字符串拼接成一个字符串进行sha1加密  
             $tmpStr =  implode($tmpArr);  
             $tmpStr =  sha1($tmpStr);  
  
             //3.将 加密后的字符串与$signature对比  
             if( $tmpStr == $signature ){  
                 return true;  
             }else{  
                 return false;  
             }  
        }  
    /**
     * 验证tooken
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function valid()  
    {  
         if ($this->checkSignature()){  
             echo $_GET["echostr"];  
         }else{  
             echo "hello world";  
         }  
    }  
}