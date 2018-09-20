<?php
namespace app\app\controller\mission_new;


interface MissionInterface
{
    /**
     * MissionInterface constructor.
     * @param array $goldRun
     */
    function _initGoldRun(&$goldRun);

    /**
     * 信息
     * @return mixed
     */
    function info();

    /**
     * 处理
     * @return mixed
     */
    function handler();
}