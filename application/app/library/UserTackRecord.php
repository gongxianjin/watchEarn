<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-23
 * Time: 下午4:25
 */

namespace app\app\library;

use app\model\GoldRun;
use app\model\UserTaskRecord;
use time\TimeUtil;
use think\Db;

class UserTackRecord
{
    /**
     * 记录用户活动或任务情况
     * @param int $user_id 用户id
     * @param string key_code 任务或者活动key_code 
     * @return array
     */
    public static function createRecord($user_id, $key_code)
    {
        if(empty($key_code) || empty($user_id)){
            return ["code"=>-1,"msg"=>"key_code或用户id不能为空"];
        }
        $taskMsg = GoldRun::where(['key_code'=>$key_code])->find();
        if(empty($taskMsg)){
            return ['code'=>-1,"msg"=>"错误key_code"];
        }
        $UserTaskRecord = new UserTaskRecord();
        $data = $UserTaskRecord->where(['user_id'=>$user_id,"key_code"=>$key_code])->find();
        if(empty($data)){
            $state = $UserTaskRecord->insert([
                "user_id"=>$user_id,
                "key_code"=>$key_code,
                "task_id"=>$taskMsg['id'],
                "number"=>1,
                "create_time"=>time(),
                "update_time"=>time(),
            ]);
        }else{
            $state =$UserTaskRecord->save([
                "number"=>["exp","number+1"],
                "update_time"=>time(),
            ],['id'=>$data['id']]);
        }
        if($state){
            return ['code'=>200,"msg"=>"success"];
        }else{
            return ['code'=>-1,"msg"=>"记录失败"];
        }
    }

}