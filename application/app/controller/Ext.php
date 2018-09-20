<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/10
 * Time: 20:33
 */
namespace app\app\controller;

use think\Controller;

class Ext extends Controller
{
    function update()
    {
        $retData = [
            'version_code' => 105,
            'version_name' => '1.0.5',
            'apk_size' => '14MB',
            'apk_name' => 'Watch And Earn',
            'apk_url' => "http://android.news.88acca.com/package/juNews.apk",
            "google_play_href" => "https://play.google.com/store/apps/details?id=com.sven.huinews.international",
            "must_update" => "1",
            "version_msg" =>" 
            <!--1:优化界面显示。-->
             <!--2:修改已知BUG。-->",

        ];

        header ("content-type: text/xml");
        header("Content-Type:text/xml");

        return $this->arrayToXml($retData);
    }


    function arrayToXml($arr){
        $xml = "<update>";
        foreach ($arr as $key=>$val){
            if(is_array($val)){
                $xml.="<".$key.">".$this->arrayToXml($val)."</".$key.">";
            }else{
                $xml.="<".$key.">".$val."</".$key.">";
            }
        }
        $xml.="</update>";
        return $xml;
    }
}