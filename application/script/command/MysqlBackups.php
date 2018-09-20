<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/3
 * Time: 14:46
 */

namespace app\script\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class MysqlBackups extends Command
{
    protected function configure()
    {
        $this->setName('mysql')
            ->setDescription('Once a day');
    }

    protected function execute(Input $input, Output $output)
    {
        $backupPath = ROOT_PATH . 'backups/';

        //video_visit 保留七天数据  文件储存30天数据
        $saveTime = strtotime(date('Y-m-d')) - 7 * 24 * 3600;
        $videoVisitPath = $backupPath . 'video_visit/';
        $fileList = $this->dirFile($videoVisitPath);
        $fileNum = count($fileList);
        if($fileNum >= 30){
            $rmNum = $fileNum - 29;
            for($i = 0;$i<$rmNum;$i++){
                unlink($fileList[$i]['path']);
            }
        }


    }


    //目录中的文件
    function dirFile($dir)
    {
        $dh = @opendir($dir); // 打开目录，返回一个目录流
        $return = array();
        while ($file = @readdir($dh)) { // 循环读取目录下的文件
            if ($file != '.' and $file != '..') {
                $path = $dir . $file; // 设置目录，用于含有子目录的情况
                if (is_file($path)) {
                    $temp['time'] = filemtime($path); // 获取文件最近修改日期
                    $temp ['path'] = $path;
                    $return [] = $temp;
                }
            }
        }
        @closedir($dh); // 关闭目录流

        array_multisort(array_column($return,'time'),SORT_ASC,$return);
        return $return; // 返回文件数组
    }

}