<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 11:00
 */

namespace app\script\command;

use app\common\model\Redis;
use app\model\BalanceLog;
use app\model\ConvertLog;
use app\model\User;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Repair extends Command
{
    protected function configure()
    {
        $this->setName('Repair')
            ->setDescription('Once');
    }

    protected function execute(Input $input, Output $output)
    {
        $logInfo = [
            // 日志记录方式，内置 file socket 支持扩展
            'type'  => 'File',
            // 日志保存目录
            'path'  => LOG_PATH,
            // 日志记录级别
            'level' => ['log','info','debug']
        ];

        $start = 1533204000;
        $end = 1533207600;
//        $sql = "select count(user_id) as num,user_id,id from hs_convert_log where log_time >$start and log_time < $end GROUP BY `user_id`;";

        $convert = new ConvertLog();
        $balance = new BalanceLog();
        $user = new User();
        $list = $convert->field('count(id) as num,user_id')->where('log_time','between',"$start,$end")->group('user_id')->select();
//        $list = Db::query($sql);
        foreach ($list as $val){
            $runNum = $val['num'] - 1;
            if($runNum > 0){
                Db::startTrans();
//                $runNum = $val['num'] - 1;
                $tempList = Db::name('convert_log')->where(['log_time'=> ['between',"$start,$end"],'user_id' => $val['user_id']])->order('log_time desc')->limit(0,$runNum)->select();
                $money = 0.00;
                $ids = [];
                foreach ($tempList as $vo){
                    $money += $vo['balance'];
                    $ids[] = $vo['id'];
                }
                $convert->where(['id' => ['in',$ids]])->delete();

                $tempList = Db::name('balance_log')->where(['create_time'=> ['between',"$start,$end"],'user_id' => $val['user_id']])->order('create_time desc')->limit(0,$runNum)->select();
                $ids = [];

                foreach ($tempList as $vo){
                    $ids[]= $vo['id'];
                }
                $balance->where(['id' => ['in',$ids]])->delete();

                $user->where(['c_user_id' => $val['user_id']])->update(['balance'=>['exp','balance-'.$money],'total_balance'=>['exp','total_balance-'.$money]]);
                Db::commit();

                $output->writeln("user_id : ".$val['user_id'] . "    repair success !  money :" . $money);
            }
        }

        $output->writeln("all success!");
    }

}