<?php

namespace app\model;

use think\Model;

class Domain extends Model
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