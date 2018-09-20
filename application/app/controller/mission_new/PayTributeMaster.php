<?php

namespace app\app\controller\mission_new;



use app\app\library\Gold;

use app\model\GoldRun;

use think\Request;

use app\model\UserApprentice;



class PayTributeMaster 

{

    const GOLD_SEARCH_RUN_CODE = 'pay_tribute_master';

    const GOLD = 1000;//进贡数达到比例

    protected $goldRun = null;

    /**

     *徒弟给师傅进贡1000金币，徒弟获得200金币 任务

     * @param  模型，引用传递

     * @param  查询条件

     * @param int  每页查询条数

     * @return 返回

     */

    static function run($param)

    {

        //任务信息

        $goldRun= GoldRun::find(26);

        if(!$goldRun){

            return ['code' => 13000, 'msg' => '活动不存在'];

        }

        if(!$goldRun['is_activation']){

            return ['code' => 13000, 'msg' => '活动结束'];

        }

        if(!$goldRun['expire_time']>time()){

            return ['code' => 13000, 'msg' => '活动过期'];

        }

        //获得徒弟为是否进贡的金币数量,

        $gold_tribute_to_father = UserApprentice::where(['master_user_id' => $param['master_user_id'], 'apprentice_user_id' => $param['apprentice_user_id']])->value('gold_tribute_total');

        //

        if ($gold_tribute_to_father > 0){

           // $before = $gold_tribute_to_father / self::GOLD;

            $after = ($gold_tribute_to_father + $param['father_gold_tribute']) / self::GOLD;

            if (floor($after) == $after) {

                $data = [

                    "user_id"=>$param['apprentice_user_id'],

                    "gold_tribute"=>$goldRun['gold_flag'],

                    "type"=>$goldRun['id'],

                    "status"=>2,//暂不发放

                    "type_key"=>$goldRun['key_code'],

                    "title"=>"每贡献给师傅1000金币获得的金币"

                ];

                \app\app\library\Gold::addUserGold($data);

            }

        }

    }



}

