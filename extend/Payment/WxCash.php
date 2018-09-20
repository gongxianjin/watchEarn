<?php
/**
 * 提现
 * @param  模型，引用传递
 * @param  查询条件
 * @param int  每页查询条数
 * @return 返回
 */
namespace Payment;
use think\Db;

class WxCash {
    /**
     * 微信提现
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function cash($order=[]){
       if(empty($order)){
            exit("错误");
        }
        $cashmodel = $order['cashmodel'];
        $paymentMsg = config('WX_PAYMENT');
        if($cashmodel == 1){
            $payMsg = $paymentMsg['open_platform'];
        }
        if($cashmodel == 2){
            $payMsg = $paymentMsg['yd_platform'];
        }
        header('content-type:text/html;charset=utf-8');
        $data['mch_appid']=$payMsg['appid'];//商户的应用appid
        $data['mchid']=$payMsg['mchid'];//商户ID
        $data['nonce_str']=$this->unicode();//这个据说是唯一的字符串下面有方法
        $data['partner_trade_no']=$order['order_number'];//这个是订单号。
        $data['openid']=$order['openid'];//这个是授权用户的openid。。这个必须得是用户授权才能用
        $data['check_name']='NO_CHECK';//这个是设置是否检测用户真实姓名的
        $data['re_user_name']='';//用户的真实名字
        $data['amount']=$order['amount']*100;//提现金额
        $data['desc']=$order['wx_desc'];//订单描述
        $data['spbill_create_ip']=$_SERVER['SERVER_ADDR'];//这个最烦了，，还得获取服务器的ip
        $secrect_key=$payMsg['secrect_key'];///这个就是个API密码。32位的。。随便MD5一下就可以了
        $data=array_filter($data);
        ksort($data);
        $str='';
        foreach($data as $k=>$v) {
            $str.=$k.'='.$v.'&';
        }
        $str.='key='.$secrect_key;
        $data['sign']=md5($str);
        $xml=$this->arraytoxml($data);
        $url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $res=$this->curl($xml,$url,$payMsg);
        $return=$this->xmltoarray($res);
        $re_data=['code'=>-1,"msg"=>"失败"];
        if(empty($return)){
            $error['draw_flag'] = "Sign1";
            $error['err_code_des'] = "签名或者其他错误";
        }else{
            if($return['return_code'] == "SUCCESS"){
                if($return['result_code'] == "SUCCESS"){
                    //成功
                    $re_data['code'] = 200;
                    $re_data['msg']="成功";
                    $error['draw_flag'] = $return['result_code'];
                    $error['err_code_des'] = $return['result_code'];
                    $error['err_code_des'] = $return['result_code'];
                }else{
                    //其他失败
                    $error['draw_flag'] =isset($return['err_code'])?$return['err_code']:$return['result_code'];
                    $error['err_code_des'] = $return['err_code_des'];
                }
            }else{
                $error['draw_flag'] = "Sign1";
                $error['err_code_des'] = "签名或者其他错误";
            }
        }
        $re_data['data'] = $error;
        return $re_data;
    }
    /**
     * 唯一的字符串
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    private function unicode() {
        $str = uniqid(mt_rand(),2);
        $str=sha1($str);
       return md5($str);
    }
    /**
     * 组装xml请求
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    private function arraytoxml($data){
        $str='<xml>';
        foreach($data as $k=>$v) {
            $str.='<'.$k.'>'.$v.'</'.$k.'>';
        }
        $str.='</xml>';
        return $str;
    }
    /**
     * xml解析
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
   private  function xmltoarray($xml) { 
     //禁止引用外部xml实体 
        libxml_disable_entity_loader(true); 
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA); 
        $val = json_decode(json_encode($xmlstring),true); 
        return $val;
    } 
    /**
     * 发起请求
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
   private  function curl($param="",$url,$apiclient) {
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();                                      //初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);                 //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);           // 增加 HTTP Header（头）里的字段 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch,CURLOPT_SSLCERT,getcwd().$apiclient['wx_pay_cert_path']); //这个是证书的位置
        curl_setopt($ch,CURLOPT_SSLKEY,getcwd().$apiclient['wx_pay_key_path']); //这个也是证书的位置
        $data = curl_exec($ch);   
        curl_close($ch);
        return $data;
    }
    
}