<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\addons\AddonException;
use think\addons\Service;
use think\Config;
use think\Exception;

/**
 * 插件管理
 *
 * @icon fa fa-circle-o
 */
class Weixin extends Backend
{

    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model("Weixin");
    }

    

}
