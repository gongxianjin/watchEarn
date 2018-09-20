<?php
namespace app\model;

use think\Model;
use think\Db;

/**
 *广告域名
 * @param  模型，引用传递
 * @param  查询条件
 * @param int  每页查询条数
 * @return 返回
 */
class AdSource extends Model
{

	const BE_RELEASED = 1;//待发布
    const ENABLE = 2; //启用，正常
    const DISABLE = 3;//禁用
    static public $ad_post_url =[];

	static public  function getSource($count=5,$flag=true,$eqment=[]){
        self::$ad_post_url = config("AD_POST_URL");
        $package_name = config("PACKIAHE_NAME");
        $eqment['os']=strtolower($eqment['os']);
        $eqment['package_name'] = $package_name[$eqment['os']];
        $flag = false;
		$map['a.status'] = self::ENABLE;//广告资源正常
        $map['d.status'] = AdType::ENABLE;//广告类型为正常
		$list  = self::alias('a')->join("__AD_TYPE__ d","d.id = a.ad_type_id")->where($map)->order("rand()")->limit($count)->field("a.*")->select();
        $list =collection($list)->toArray();
		if(!empty($list)){
			if($flag == true){
				foreach ($list as $e => &$l) {
					$l['title'] =json_decode($l['title'],true);
					$l['img'] =json_decode($l['img'],true);
					$l['open_browser'] =1;
				}
			}else{
                foreach ($list as $e => &$l) {
                    switch ($l['ad_type_id']) {
                        case '1000'://fb
                            $title = json_decode($l['title'],true);
                            $l['title'] = $title[array_rand($title,1)];
                            $l['img'] = json_decode($l['img'],true)[0];
                            $l['ad_type'] = "self";
                            $l['open_browser'] = 0;
                            $l['ad_otherMsg'] = [];
                            break;
                         case '1001'://google
                             $title = json_decode($l['title'],true);
                             $l['title'] = $title[array_rand($title,1)];
                             $l['img'] = json_decode($l['img'],true)[0];
                             $l['ad_type'] = "self";
                             $l['open_browser'] = 0;
                             $l['ad_otherMsg'] = [];
                            break;
                    }
                }
			}
		}
        return $list;
	}

	/**
	 * 获取点冠广告 
	 * @param  模型，引用传递
	 * @param  查询条件
	 * @param int  每页查询条数
	 * @return 返回
	 */
	static public function Getdianguan($data){
		extract($data);
        $ad_post_url =self::$ad_post_url;
        $url = $ad_post_url['dianguan'];
        switch ($os) {
            case 'android':
                $os_type = 1;
                break;
            case 'ios':
                $os_type = 2;
                break;
            default:
                $os_type = 3;
                break;
        }
        // (1. WIF 2. UNKN 3. 2G 4. 3G 5.4G) 
        $network_arr = ['1'=>1,'2'=>2,'2'=>3,'3'=>4,'4'=>5];
        $network_key = array_search($network_type,$network_arr);
        if($network_key){
            $network_type = $network_arr[$network_key];
        }else{
            $network_type = 2;
        }
        $data = [
            //媒体信息
            'media'=>[
                'type'=>$os!='web'?1:2,// 1为 APP 2 h5
                'app'=>['package_name'=>$package_name,'app_version'=>$version],
                'site'=>['domain'=>'','urls'=>'','title'=>'','keywords'=>''],
                'browser'=>['user_agent'=>$user_agent]
            ],
            //设备信息
            'device'=>[
                'id_idfa'=> $meid,
                'id_imei'=>$meid,
                'height'=>320,
                'width'=> 568,
                'brand'=>$mobile_brand,
                'model'=> $mobile_model,
                'os_version'=>$mobile_version,
                'os_type'=>$os_type,
            ],
            //网络环境
            'network'=>[
               'type'=>$network_type,
                'ip'=> $ip,
            ],
            //客户端类型
            'client'=>[
                'type'=>3,
                'version'=>'1.6.4'//写死
            ],
            //广告位信息
            'adslot'=>[
                'id'=>$materiel_id,
                'type'=>2,
                'height'=>100,
                'width'=>300,
                'capacit'=>1,
                'page_num'=>empty($page)?1:intval($page),
                'book_id'=>''
            ]
        ];
        try {
            $res = json_decode(curlhttp($url,"",json_encode($data),'POST'), true);
            if(!isset($res['success'])){
                    throw new \Exception("广告获取失败", 1);
            }
            if(!$res['success']){
                    throw new \Exception("广告获取失败", 1);
            }
            $ads = $res['ads'];
            $material_type = $ads['material_type'];
            $content =  json_decode($res['ads']['native_material']['image_snippet'],true);
            $r=[
                "ad_type"=>"aiclk",
                "img"=>$content['url'],
                "c_url"=>$content['c_url'],
                "width"=>$content['width'],
                "height"=>$content['height'],
                "title"=>$content['title'],
                "desc"=>$content['desc'],
                "ad_otherMsg"=>[
                    "imp"=>$content['imp'],
                    "clk"=>$content['clk'],
                ],
            ];
            switch ($ads['native_material']['interaction_type']) {
                case '1':
                    $r['open_type'] = 1;//浏览器打开
                    break;
                case '2':
                    $r['open_type'] = 3;//下载
                    break;
                default:
                    $r['open_type'] = 1;
                    break;
            }
            return $r;
        } catch (\Exception $e) {

            
           return false;
        }
         
        
	}
	/**
	 * 获取瑞狮广告
	 * @param  模型，引用传递
	 * @param  查询条件
	 * @param int  每页查询条数
	 * @return 返回
	 */
	static public function Ruishi($param){
        //pp($param);
        extract($param);
        $ad_post_url =self::$ad_post_url;
        $url = $ad_post_url['ruishi'];
        //pp($url);
        //网络
         // 1->WIFI,0->UNKNOWN 2->2G ,3->3G,4->4G
        $network_arr = ['0'=>0,'1'=>1,'2'=>3,'3'=>3,'4'=>4];
        $network_key = array_search($network_type,$network_arr);
        if($network_key){
            $network_type = $network_arr[$network_key];
        }else{
            $network_type = 0;
        }
        switch ($os) {
            case 'android':
                $os_type = 1;
                break;
            case 'ios':
                $os_type = 2;
                break;
            default:
                $os_type = 3;
                break;
        }
        $param_ = [
            'tagid' => $materiel_id,
            'appid' =>  372,
            'appname' =>  urlencode('淘视界'),
            'pkgname' =>  $package_name,
            'appversion' => $version,
            'os' =>  $os_type,
            'osv' => $mobile_version ,
            'carrier' =>1,//0：其他 1：移动2：联通 3：电信
            'conn' => $network_type,
            'ip' => $ip,
            'make' =>  $mobile_brand,
            'model' => $mobile_model,
            'imei' => $meid,
            'idfa' =>  $meid,
            'anid' => $meid,
            'sw' =>  640,
            'sh' => 1080,
            'devicetype' => 1,#1：手机2：平板
            //'ua' =>  urlencode($_SERVER['HTTP_USER_AGENT']),
            'ua' =>  urlencode('Mozilla/5.0 (iPhone 84; CPU iPhone OS 10_3_3 like Mac OS X) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.0 MQQBrowser/7.8.0 Mobile/14G60 Safari/8536.25 MttCustomUA/2 QBWebViewType/1 WKType/1'),
            'adt' => 3
        ];
    
        $param_ = http_build_query($param_);

        $url = $url.'?'.$param_;
    
        $result = curlhttp($url);
     
        try {
            $result = json_decode($result,true);
        } catch (\Exception $e) {
            return false;
        }
        
       // pp($result);
        ###################
        #开屏广告特殊处理 - 开始
        /*if($ad_type == 4){
            try {
                $return = [
                    'title'  =>  '',
                    'description'  =>  '',
                    'images'  =>  [$result['imgurl']],
                    'url'  =>  $result['ldp'], #落地页
                    'clk'  =>  $result['clk_tracking'], #点击监听
                    'imp'  =>  $result['imp_tracking'], #曝光监听
                    'img_type'  =>  $ad_type, #1单图（尺寸不大），2三图，3大图
                    'ad_type'  =>  'vlion',
                ];
                
                return $return;
            } catch (\Exception $e) {
                return false;
            }
        }*/
        #开屏广告特殊处理 - 结束
        ####################
        if(empty($result['nativead'])){
            return false;
        }
        $img =$result['nativead']['img']['0'];
        if(!isset($result['nativead']['ldp']) && isset($result['nativead']['app_download_url'])){
            $url = $result['nativead']['app_download_url'];
        }else{
            $url = $result['nativead']['ldp'];
        }
        try {
            $r=[
                "ad_type"=>"vlion",
                "img"=>$img['url'],
                "c_url"=>$url,
                "width"=>$img['w'],
                "height"=>$img['h'],
                "title"=>$result['nativead']['title'],
                "desc"=>$result['nativead']['desc'],
                "ad_otherMsg"=>[
                    "imp"=>$result['imp_tracking'],
                    "clk"=>$result['clk_tracking'],
                ],
            ];
            switch (isset($result['interact_type'])?$interact_type:1) {
                case '1':
                    $r['open_type'] = 1;//浏览器打开
                    break;
                case '2':
                    $r['open_type'] = 3;//下载
                    break;
                default:
                    $r['open_type'] = 1;
                    break;
            }
            return $r;
        } catch (\Exception $e) {
            //throw $e;
            return false;
        }
        return $return;
	}


		
}