<?php
namespace app\app\controller\mission_new;

class Demo implements MissionInterface
{
    protected $goldRun = null;

    function _initGoldRun(&$goldRun)
    {
        $this->goldRun = $goldRun;
    }

    function info()
    {
        print_r($this->goldRun);

        exit('info access, exit...');
    }

    function handler()
    {
        exit('handler access, exit...');
    }

}
