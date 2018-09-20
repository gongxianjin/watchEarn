<?php

namespace app\app\controller;

use app\app\controller\mission_new\MissionInterface;

use app\model\GoldRun;

use think\Db;
use think\Exception;

use think\response\Json;

class MissionNew extends BaseController
{

    /**
     * @var MissionInterface
     */
    public $class = null;

    public function __construct()
    {
        $class = &$this->class;
        $id = input('id','');

        $goldRun = GoldRun::find($id);

        if($this->login_flag){
            if($this->userInfo['is_cross_read_level']){
                return out(['gold_flag'=>0,'count'=>0],'10002','Suspicious activiity detected, your account has been blocked!');
            }
        }

        //不间断访问多余300任务 封号
        $cacheKey = "TaskUserCache__" . $this->user_id;
        $cacheNum = cache($cacheKey);
        if(empty($cacheNum)){
            cache($cacheKey,1,['expire' => 1800]);
        }else{
            $cacheNum = intval($cacheNum);
            if($cacheNum > 100){
                $this->userModel :: getUserInfoById($this->user_id);
            }
            cache($cacheKey,$cacheNum++,['expire' => 1800]);
            if($cacheNum > 300){
                $upData = [
                    "is_cross_read_level" => 1,
                    "gold_flag" => 0,
                    "total_gold_flag" =>0,
                    "frozen_balance" =>0,
                    "balance"=>0,
                    "total_balance"=>0,
                ];
                Db::table('user')->where(['c_user_id' => $this->user_id])->update($upData);
                return out(['gold_flag'=>0,'count'=>0],'10002','Suspicious activiity detected, your account has been blocked!');
            }
        }

        if (empty($goldRun))
            throw new Exception('not found');

        $goldRun = $goldRun->toArray();

        if(!$goldRun){
            return ['code' => 13000, 'msg' => 'task is null'];
        }
        if(!$goldRun['is_activation']){
            return ['code' => 13000, 'msg' => 'task is end'];
        }
        if(!$goldRun['expire_time']>time()){
            return ['code' => 13000, 'msg' => 'task is end'];
        }

        $className =  $goldRun['key_code_class'];
        $className = '\app\app\controller\mission_new\\'.$className;

//        !class_exists($className) && exit('mission type error'.$className);
        if(!class_exists($className)){
            return ['code' => 13000, 'msg' => 'task is error'];
        }

        $class = new $className();
        if ($class instanceof MissionInterface)
            $class->_initGoldRun($goldRun);

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function info()
    {
        return $this->run('info');
    }


    /**
     * @return mixed
     * @throws \Exception
     */
    public function other()
    {
        $func = input('other','');
        $func==='' && exit;
        return $this->run($func);
    }

    /**
     * @return Json
     * @throws \Exception
     */
    public function handler()
    {
        return $this->run('handler');
    }

    /**
     * @param $func
     * @return Json
     * @throws \Exception
     */
    private function run($func)
    {
        $class = &$this->class;

        if (!method_exists($class,$func))
            exit;

        $result = $class->$func();

        if ($result instanceof Json)
            return $result;
        else
            throw new \Exception('返回错误');
    }

}
