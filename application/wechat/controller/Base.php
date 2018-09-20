<?php
namespace app\wechat\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Cookie;
use think\Session;
use Wechat\org\JSSDK2;
use app\model\User;

class Base extends Controller
{
  	private $app =[];
  	public  $unionid = "";//用户unionid
  	public  $openid ="";//用户公众号id
    public  $userMsg =[];
    public function _initialize()
    {
    	$this->app = config("WX_G_HAO_CONFIG");
       $userInfo  = Session::get("taoReceiveMsg");
        if(empty($userInfo)){
            $userInfo = $this->get_openid();
            if(empty($userInfo['unionid']) || empty($userInfo['openid'])){
                Session(null);
                $this->redirect("Reg/autherror");
            }
            Session::set("taoReceiveMsg",$userInfo);
        }
        $this->unionid = $userInfo['unionid'];
        $this->openid = $userInfo['openid'];
        $user = User::where(['unionid'=>$this->unionid])->find();
        if(empty($user)){
            Session(null);
            $this->redirect("Reg/noreg");
        }
        if(empty($user['web_openid'])){
            User::where(['c_user_id'=>$user['c_user_id']])->update(['web_openid'=>$this->openid]);
        }
        $this->userMsg = $user;
        
    }

     /**
     * 获取用户openid
    */
    public function get_openid()
    {


        $request = Request::instance();
        $code = input("code","");
        if (empty($code)) {
         // get code
            $redirect = urlencode($request->url(true));
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=". $this->app['app_id']."&redirect_uri={$redirect}&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
            $this->redirect($url);
        } else { 
        // get openid
        	$openid ="";
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->app['app_id']."&secret=".$this->app['secret']."&code={$code}&grant_type=authorization_code";
            try {
            	$res = json_decode(curlhttp($url), true);
          		$openid = $res['openid'];
          		if(!$openid){
          			throw new \Exception("错误请求", 1);
          		}
            } catch (\Exception $e) {
              $this->redirect("Reg/autherror");
            }
          	$userMsg = $this->getUniod($openid);
          	if($userMsg['code']!=1){
          		$this->redirect("Reg/autherror");
          	}
          	return ['openid'=>$userMsg['data']['openid'],'unionid'=>$userMsg['data']['unionid']];
        }
       // return ['openid'=>"oMzaL1hCYIxXTEaXNtxEHgqijiIQ",'unionid'=>"11111"];
    }

    /**
     * 获取用户uniond
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function getUniod($openid){
    	  $jssdk = new JSSDK2($this->app['app_id'], $this->app['secret']);
        $access_token = $jssdk->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
    	  $res = json_decode(curlhttp($url), true);
    	  try {
    		  if(!isset($res['subscribe'])){
    			  throw new \Exception("微信授权失败", 1);
    		  }
    		  if($res['subscribe'] !== 1){
    			  throw new \Exception("请关注微信号", 1);
    		  }
    	  } catch (Exception $e) {
    		   return  ['code'=>-1,"msg"=>$e->getMessage(),'data'=>[]];
    	  }
    	  return ['code'=>1,"msg"=>"获取成功",'data'=>$res];
    }

}