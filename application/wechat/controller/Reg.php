<?php
namespace app\wechat\controller;

use think\Controller;
use think\Request;


class Reg extends Controller
{
    
   
    /**
     * 用户未注册页面
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function noreg(){
        return $this->fetch();
    }
    /**
     * 失败
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function autherror(){
        return $this->fetch();
        

    }
}