<?php
namespace app\model;

use think\Model;

/**
 *广告域名
 * @param  模型，引用传递
 * @param  查询条件
 * @param int  每页查询条数
 * @return 返回
 */
class AdDomain extends Model
{

	public function makeUrl($url){
		//分享，显示，第三方
        $redirect_url = $this->where(['status'=>1,'type'=>1])->find();
        //$display_url = $domainModel->where(['status'=>1,'type'=>2])->find();
        $third_url = $this->where(['status'=>1,'type'=>3])->find();
        $share_url = $redirect_url['domain_name'].$url;

        return  short_durl($third_url['domain_name'].$share_url);//跳转地址

	}
}