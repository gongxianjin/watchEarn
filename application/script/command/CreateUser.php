<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 11:00
 */

namespace app\script\command;

use app\common\model\Redis;
use app\model\DummyUser;
use app\model\NewVideo;
use app\model\User;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;

class CreateUser extends Command
{
    protected function configure()
    {
        //设置执行名称
        $this->setName('createUser')
            ->setDescription('Run Once');
    }

    protected function execute(Input $input, Output $output)
    {
        $videoModel = new NewVideo();
        $userModel = new DummyUser();
        $needUser = 5000;
        $filePath = ROOT_PATH . 'data/createUser.txt';
        $users = [];
        if(file_exists($filePath)){
            $usersText = file_get_contents($filePath);
            if(!empty($usersText)){
                $users = json_decode($usersText);
            }
        }

        while (count($users) < $needUser){
            $lists = $videoModel->order('rand()')->limit(500)->select();
            $tempUser = [];
            $tempName = [];
            foreach ($lists as $val){
                $nickname = trim($val['user_nickname']);
                $lowerName = strtolower($nickname);
                if(!$nickname || !$lowerName){
                    continue;
                }
                if(!in_array($lowerName,$users) && !in_array($lowerName,$tempName)){
                    $temp['nickname'] = $nickname;
                    $temp['user_avatar'] = $val['user_avatar'];
                    $temp['create_time'] = time();
                    $tempUser[] = $temp;
                    $tempName[] = $lowerName;
                }
            }
            try{
                $output->writeln(json_encode($tempUser));
                $re = $userModel->insertAll($tempUser);
            }catch (Exception $e){
                continue;
            }


            if($re){
                $users = array_merge($users,$tempName);
                file_put_contents($filePath,json_encode($users));
            }
            sleep(1);
        }

        $output->writeln("over!");
    }

}