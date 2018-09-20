<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 18-1-23
 * Time: 下午1:40
 */

namespace app\app\library;

use app\model\GoldPassiveProductRecord;
use app\model\GoldProductRecord;
use app\model\TempUser;
use app\model\User;
use app\model\UserApprentice;
use think\Db;
use app\app\controller\mission_new\PayTributeMaster;


class Gold
{
    /**
     * 添加用户金币
     * @param int $user_id 用户id
     * @param int $gold_tribute 该用户要加的金币数
     * @param int $status 状态 1.暂不发放 2.立即发放 注意 当用户在个人中心领取了状态为未发放的金币时，会调用我下方的另外一个发放金币接口 用来改变这个发放状态
     * @param int $type 任务类型 配置的数字 如： 新手任务id
     * @param string $type_key 类型配置的英文key
     * @param string $title 产生金币的任务名称或者描述标题
     * @param int $type_task_id 任务对应记录的id，有就传，没有就为0 默认为0
     * @param int $father_gold_tribute 要贡献给师傅的金币数 默认为0 就代表不贡献给师傅
     * @param int $grandfather_gold_tribute 要贡献给师祖的金币数 默认为0 就代表不贡献给师祖
     * @param int $tribute_status 贡献的金币发放状态 默认为0即无贡献 有贡献的情况就传1或2 1.暂不发放 2.立即发放
     * @param string $tribute_title 贡献金币的任务名称或者描述标题 无贡献时为空
     * @param \Closure $func
     * @param 最后一个参数是需要跟事物操作的回调函数
     * @throws
     * @return bool
     */
   // public static function addUserGold($user_id, $gold_tribute, $status, $type, $type_key, $title, $type_task_id = 0, $father_gold_tribute = 0, $grandfather_gold_tribute = 0, $tribute_status = 0, $tribute_title = '', $func = null)
    public static function addUserGold(&$param)
    {
        extract($param);
        $type_task_id = isset($type_task_id)?$type_task_id:0;
        $father_gold_tribute = isset($father_gold_tribute)?$father_gold_tribute:0;
        $grandfather_gold_tribute = isset($grandfather_gold_tribute)?$grandfather_gold_tribute:0;
        $tribute_status = isset($tribute_status)?$tribute_status:0;
        $tribute_title = isset($tribute_title)?$tribute_title:"";
        $func = isset($func)?$func:null;
        //pp($param);die;
        // 启动事务
        Db::startTrans();
        try{
            if ( $func !== null && $func instanceof \Closure ){
                $func();
            }
            //判断是否有给师傅师祖进贡
            $is_father_contribute = $is_grandfather_contribute = 1;
            if ($father_gold_tribute > 0) {
                $is_father_contribute = 2;
            }
            if ($grandfather_gold_tribute > 0) {
                $is_grandfather_contribute = 2;
            }
            $add_gold_data = [
                "user_id"=>$user_id,
                "status"=>$status,
                "type"=>$type,
                "type_key"=>$type_key,
                "type_task_id"=>$type,
                "gold_tribute"=>$gold_tribute,
                "is_father_contribute"=>$is_father_contribute,
                "is_grandfather_contribute"=>$is_grandfather_contribute,
                "title"=>$title,
                "create_time"=>time(),
                "update_time"=>time(),
                "pid"=>0,
                "create_type"=>1,
                "create_user_id"=>0,
                "user_r_type"=>0,
            ];
            //添加金币产生表
            if(!$gold_product_record_id = self::add_gold_product_record($add_gold_data)){
                throw new \Exception("添加金币失败", 1);
            }
            if ($status == 2){
                //更新用户金币数
                if(!self::update_user_gold($user_id, $gold_tribute)){
                    throw new \Exception("金币添加失败", 1);
                }
            }
            //是否给师傅进贡
            if ($is_father_contribute == 2){
                $user_father_id = User::where('c_user_id', $user_id)->value('user_father_id');
                if ($user_father_id && $father_gold_tribute > 0 ){
                    //添加金币被动产生表
                    $father_add_gold_data = [
                        "user_id"=>$user_father_id,
                        "status"=>$tribute_status,
                        "type"=>$type,
                        "type_key"=>"son_".$type_key,
                        "type_task_id"=>$type,
                        "gold_tribute"=>$father_gold_tribute,
                        "is_father_contribute"=>0,
                        "is_grandfather_contribute"=>0,
                        "title"=>$tribute_title,
                        "create_time"=>time(),
                        "update_time"=>time(),
                        "pid"=>$gold_product_record_id,
                        "create_type"=>2,
                        "create_user_id"=>$user_id,
                        "user_r_type"=>1,
                    ];
                    if(!self::add_gold_product_record($father_add_gold_data)){
                        throw new \Exception("为师傅进贡失败", 1);
                    }
                    //立即发放金币
                    if ($tribute_status == 2){
                        //更新用户金币数
                        self::update_user_gold($user_father_id, $father_gold_tribute);
                         //更新徒弟为师傅贡献的金币数量
                        self::update_user_apprentice_gold($user_father_id, $user_id, $father_gold_tribute);
                        //触发 每给师傅贡献1000个金币，徒弟会获得一个红包 活动
                        $params['master_user_id'] = $user_father_id;
                        $params['apprentice_user_id'] = $user_id;
                        $params['father_gold_tribute'] = $father_gold_tribute;
                        //(new PayTributeMaster())->run($params);
                    }
                }
            }
            //更新师祖的金币数量
            if ($is_grandfather_contribute == 2){
                $user_grandfather_id = User::where('c_user_id', $user_id)->value('user_grandfather_id');
                if ($user_grandfather_id && $grandfather_gold_tribute) {
                    //添加金币被动产生表
                    $grandfather_add_gold_data = [
                        "user_id"=>$user_grandfather_id,
                        "status"=>$tribute_status,
                        "type"=>$type,
                        "type_key"=>'grandson_'.$type_key,
                        "type_task_id"=>$type,
                        "gold_tribute"=>$grandfather_gold_tribute,
                        "is_father_contribute"=>0,
                        "is_grandfather_contribute"=>0,
                        "title"=>$tribute_title,
                        "create_time"=>time(),
                        "update_time"=>time(),
                        "pid"=>$gold_product_record_id,
                        "create_type"=>2,
                        "create_user_id"=>$user_id,
                        "user_r_type"=>2,
                    ];
                    if(!self::add_gold_product_record($grandfather_add_gold_data)){
                        throw new \Exception("为师傅进贡失败", 1);
                    }
                    if ($tribute_status == 2){
                        //更新用户金币数
                        self::update_user_gold($user_grandfather_id, $grandfather_gold_tribute);
                        //更新徒孙为师祖贡献的金币数量
                        self::update_user_apprentice_gold($user_grandfather_id, $user_id, $grandfather_gold_tribute);
                    }
                }
            }
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 通过记录的id发放金币
     * @param int $user_id 用户id
     * @param int $record_type 类型 1.金币主动产生表 2.金币被动产生表
     * @param int $record_id 对应类型的记录id
     * @param int $gold_tribute 金币数
     */
    public static function giveOutGoldById($user_id, $record_type, $record_id, $gold_tribute)
    {
        // 启动事务
        Db::startTrans();
        try {
            if ($record_type == 1) {
                //更新金币发放状态
                GoldProductRecord::update(['status' => 2], ['id' => $record_id]);
            }
            //更新用户的金币数
            self::update_user_gold($user_id, $gold_tribute);
            // 提交事务
            Db::commit();

            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 发放金币
     * @param int $user_id 用户id
     * @param int $count 未领取的金币记录数量
     * @throws
     * @return array
     */
    public static function giveOutGold($user_id, $count)
    {
        $is_all = false;
        if ($count >= 5){
            $is_all = true;
        }
        // 启动事务
        Db::startTrans();
        try {
            $title = '';
            $gold_tribute = 0;
            $record = self::getUnsentGoldRecordByUserId($user_id);
            if ($is_all){
                $gold_tribute_total = 0;
                if (!empty($record['goldProductRecord'])){
                    $record_id_array = array();
                    foreach ($record['goldProductRecord'] as $k => $v){
                        $gold_tribute_total = $gold_tribute_total + (int)$v['gold_tribute'];
                        $record_id_array[] = $v['id'];
                        if($v['create_type'] == 2 && $v['create_user_id'] >0){
                            self::update_user_apprentice_gold($v['user_id'], $v['create_user_id'], $v['gold_tribute']);
                        }
                    }
                    //更新金币发放状态
                    GoldProductRecord::update(['status' => 2], ['id' => ['in', $record_id_array]]);
                }
                if ($gold_tribute_total){
                    $title = '所有红包的总收益';
                    $gold_tribute = $gold_tribute_total;
                    //更新用户的金币数
                    self::update_user_gold($user_id, $gold_tribute_total);
                }
            }else {
                //非全部一次领取情况，则先领取主动产生金币的记录，再领取被动产生的记录
                if (!empty($record['goldProductRecord'])){
                    $goldProductRecord = array_shift($record['goldProductRecord']);
                    if($goldProductRecord['create_type'] == 2 && $goldProductRecord['create_user_id'] >0){
                        self::update_user_apprentice_gold($goldProductRecord['user_id'], $goldProductRecord['create_user_id'], $goldProductRecord['gold_tribute']);
                    }
                    //更新金币发放状态
                    GoldProductRecord::update(['status' => 2], ['id' => $goldProductRecord['id']]);
                }
                if (!empty($goldProductRecord['gold_tribute'])){
                    $title = $goldProductRecord['title'];
                    $gold_tribute = $goldProductRecord['gold_tribute'];
                    //更新用户金币数
                    self::update_user_gold($user_id, $goldProductRecord['gold_tribute']);
                }
            }
            // 提交事务
            Db::commit();
            return ['title' => $title, 'gold_tribute' => $gold_tribute, 'is_all' => $is_all];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 通过用户id获得用户未发放的任务金币记录
     * 注意用户未发放的任务金币记录有种类型
     * 第一种是用户自己操作主动产生的金币未发放
     * 第二种是他的徒弟贡献给他的，这种他被动产生金币未发放
     * @param int $user_id 用户id
     * @return array
     */
    private static function getUnsentGoldRecordByUserId($user_id)
    {
        //主动产生金币记录
        $goldProductRecord = GoldProductRecord::where(['user_id' => $user_id, 'status' => '1'])->field('*')->select();
      

        return ['goldProductRecord' => $goldProductRecord, 'goldPassiveProductRecord' =>[]];
    }

    /*
     * 获取未发放金币的数量
     */
    public static function getUnsetGoldCount($user_id)
    {
        //主动产生金币未领取记录数量
        $goldProductRecordCount = GoldProductRecord::where(['user_id' => $user_id, 'status' => '1'])->count();
      
        return $goldProductRecordCount;
    }

    /*
     * 添加金币产生表
     */
    private static function add_gold_product_record($data)
    {
       
        $goldProductRecord = new GoldProductRecord($data);
        $goldProductRecord->save();
        return $goldProductRecord->id;
    }

    /*
     * 更新用户金币数据
     */
    private static function update_user_gold($user_id, $gold_tribute)
    {
        $up = [
           "total_gold_flag"=>['exp',"total_gold_flag+$gold_tribute"],
           "gold_flag"=>['exp',"gold_flag+$gold_tribute"],
        ];
        if (User::where('c_user_id', $user_id)->count('id')){
            $res = User::where('c_user_id', $user_id)->update($up);
        }else {
            $res = TempUser::where('c_user_id', $user_id)->update($up);
        }
        return $res;
    }

    /*
     * 添加金币被动产生表 弃用
     */
    static function add_gold_passive_product_record($gold_product_record_id, $product_user_id, $master_type, $master_user_id, $gold_tribute, $status = 1, $title = '')
    {
            return false;
        /*$data = [
            'gold_product_record_id' => $gold_product_record_id,
            'product_user_id' => $product_user_id,
            'master_type' => $master_type,
            'master_user_id' => $master_user_id,
            'gold_tribute' => $gold_tribute,
            'status' => $status,
            'title' => $title
        ];
        $goldPassiveProductRecord = new GoldPassiveProductRecord($data);
        $goldPassiveProductRecord->save();
        return $goldPassiveProductRecord->id;*/
    }

    /*
     *  更新徒弟为师傅贡献的金币数量
     */
    private static function update_user_apprentice_gold($master_user_id, $apprentice_user_id, $gold_tribute)
    {
        UserApprentice::where(['master_user_id' => $master_user_id, 'apprentice_user_id' => $apprentice_user_id])->setInc('gold_tribute_total', $gold_tribute);
    }
}