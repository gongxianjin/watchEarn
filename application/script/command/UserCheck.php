<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 11:00
 */

namespace app\script\command;

use app\common\model\Redis;
use app\common\service\UserService;
use app\model\User;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;

class UserCheck extends Command
{
    protected function configure()
    {
        //设置执行名称  每天早上4点执行
        $this->setName('usercheck')
            ->setDescription('Run all the time');
    }

    protected function execute(Input $input, Output $output)
    {
        $url = config('recommend_http');
//        $url = "192.168.0.58";
        $addr = "/open/platform/reg";
        $url = $url . $addr;
        $pageSize = 500;
        $model = new User();
        $service = new UserService();

        while (true){
            $users = $model->where(['unique' => ['in',['',null]]])->order('id desc')->limit($pageSize)->select();
            if(empty($users)){
                break;
            }
            foreach ($users as $val){
                $unique = uuid();
                $result = $service->createUser($unique, $val->mail,$val->nickname,$val->headimg);

                if($result){
                    $val->unique = $unique;
                    $res = $val->save();
                    if(!$res){
                        $output->writeln($val->c_user_id ." error!");
                    }else{
                        $output->writeln($val->c_user_id ." success!");
                    }
                }else{
                    $output->writeln($val->c_user_id ." request error!");
                }
            }
        }

        $output->writeln("over!");
    }

}