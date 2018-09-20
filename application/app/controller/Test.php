<?php
namespace app\app\controller;

use think\Request;
use app\app\controller\BaseController;
use app\model\ActivatePush;


class Test
{

    public function date()
    {

        $a = mktime(0,2,0,4,2);
        $b = mktime(0,1,0,4,5);

        $days = intval(($b - $a) / 3600 / 24)+1;

        echo $days;

    }

    
   
}
