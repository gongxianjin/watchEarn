<?php
namespace app\app\controller;

use think\Db;

class Search extends BaseController
{

    public function getkeyword()
    {
//        $list = Db::name("hot_keyword")->limit(0,8)->field("keyword")->select();
        return out([]);

    }

}
