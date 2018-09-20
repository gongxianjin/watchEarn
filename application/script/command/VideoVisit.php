<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/27
 * Time: 11:00
 */

namespace app\script\command;

use app\common\model\Redis;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class VideoVisit extends Command
{
    protected function configure()
    {
        //设置执行名称  每天早上4点执行
        $this->setName('videovisit')
            ->setDescription('Execute at 4 points per day');
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
        Config::set('log',$logInfo);
        $output->writeln("videoVisit start!");
//        $VideoVisit = new \app\model\VideoVisit();
        $VideoVisit = Db::name('video_visit');

        $redis = new Redis();
        $keyPer = 'Vi_Video_';
        $queueKey = 'queueVideoVisit';

        $isOK = false;
        $pageSize = 200;
        $page = 1;
        while (!$isOK)
        {
            //队列长度小于200 判断执行成功 不再执行
            $listNum = $redis->lenth($queueKey);
            if($listNum < $pageSize){
                break;
            }

            $insertData = [];
            for ($i=0;$i<$pageSize;$i++){
                //头部进 尾部出
                $jsonTemp = $redis->lpop($queueKey);
                if(empty($jsonTemp)){
                    $isOK = true;
                    break;
                }
                if(!is_array($jsonTemp)){
                    $jsonTemp = json_decode($jsonTemp,true);
                }
                $tempKey = $keyPer . $jsonTemp['user_id'] . '_' . $jsonTemp['video_id'];
                $tempNum = $redis->get($tempKey);
                if(!empty($tempNum) && $tempNum > 0){
                    //组装统计数据
                    $tempData = [
                        'user_id'=>$jsonTemp['user_id'],
                        'video_id'=>$jsonTemp['video_id'],
                        'create_time'=>$jsonTemp['time'],
                        'count'=>$tempNum
                    ];
                    $insertData[] = $tempData;
                }

                //执行完毕之后删除key
                $redis->delete($tempKey);
            }

            if(!empty($insertData)){
//                $res = $VideoVisit->insertAll($insertData);
                $sql = "insert into hs_video_visit(`video_id`,`user_id`,`create_time`,`count`) values ";
                foreach ($insertData as $key => $val){
                    if($key == 0){
                        $sql .= "(".$val['video_id'].",".$val['user_id'].",".$val['create_time'].",".$val['count'].")";
                    }else{
                        $sql .= ",(".$val['video_id'].",".$val['user_id'].",".$val['create_time'].",".$val['count'].")";
                    }
                }
                trace($sql,'debug');
                $res = Db::execute($sql);

                if(!$res){
                    trace($sql,'error');
                    $output->writeln(" handle  success , exit !");
                    exit();
                }
                $output->writeln(" page $page handle success");
            }else{
                $output->writeln(" page $page handle error!");
            }

            $page++;
            sleep(3);
        }

        $output->writeln("all success!");
    }

}