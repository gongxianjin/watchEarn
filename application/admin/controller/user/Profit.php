<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Controller;
use think\Request;
use app\model\UserCashRecord;
use app\model\User as UserModel;
use app\model\GoldProductRecord;
use app\model\GoldRun;

/**
 * 微信配置管理
 *
 * @icon fa fa-circle-o
 */
class Profit extends Backend
{

    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new GoldProductRecord();
    }
    /**
     * 查看
     */
    public function index($ids = null)
    {

        $ids = input("ids");

        if ($this->request->isAjax())
        {

            $userInfo =(new UserModel())->where(['c_user_id'=>$ids])->field('c_user_id,nickname')->find();
            $user_id = $userInfo['c_user_id'];
            $GoldProductRecord = new GoldProductRecord();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $task = (new GoldRun())->column("key_code,title");

            $total = $this->model->where($where)->where(['user_id'=>$user_id])->count("id");

            $list = $this->model
                    ->where($where)
                    ->where(['user_id'=>$user_id])
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            if(!empty($list)){
                if(!empty($list)){
                    foreach ($list as $key => &$value) {
                        $value['nickname'] = $userInfo['nickname'];
                        $value['taskname'] = isset($task[$value['type_key']])?$task[$value['type_key']]:$value['type_key'];
                    }
                }
            }

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->assign("ids",$ids);
        return $this->view->fetch();
    }


    public function distinctSum($ids = null){

        //查出最近一周该用户获得金币情况

        //查出userid,根据uid找到所有子节点.
        $UserCashRecordModel = new UserCashRecord();
        $UserCashRecord = $UserCashRecordModel->getOrderRcordByid($ids);
        if (!$UserCashRecord)
            $this->error(__('No Results were found'));
        $user_id = $UserCashRecord['user_id'];
        $userModel = new UserModel();
        $userInfo = $userModel->where(['c_user_id'=>$user_id])->find();

        //设置开始查询时间标记
        $begin_time = mktime(0,0,0,date('m'),date('d')-7,date('Y'));

        //设置结束查询时间标志
        $end_time = mktime(23,59,59,date('m'),date('d'),date('Y'));

        $dailyUserReadItems = (new GoldProductRecord())->getDailyItemUsage(
            $user_id
            ,'usual_read'
            ,$begin_time
            ,$end_time
            ,1
            ,false
        );

        
        foreach ($dailyUserReadItems as $key=>$item){

            $conditions = 'date = '.$item->toArray()['date'];

            //查出广告视频个数并贴到每日观看金币个数中
            $viewDailyUserItems = (new GoldProductRecord())->getDailyItemUsage(
                $user_id
                ,'view_ad'
                ,$begin_time
                ,$end_time
                ,1
                ,$conditions
            );
            if(!empty($viewDailyUserItems)){
                $dailyUserReadItems[$key]['view_count'] = $viewDailyUserItems[0]->toArray()['baseCount'];
            }else{
                $dailyUserReadItems[$key]['view_count'] = 0;
            }

            //查出每日观看新闻奖励平均间隔时间并贴到基数中
            $viewDailyDetailItems = (new GoldProductRecord())->getDailyItemDetailUsage(
                $user_id
                ,'usual_news_read'
                ,$begin_time
                ,$end_time
                ,1
                ,$conditions
            );

            if(!empty($viewDailyDetailItems)){
                $dailyUserReadItems[$key]['avgNewsTime'] = round(($viewDailyDetailItems[0]->toArray()['maxTime'] - $viewDailyDetailItems[0]->toArray()['minTime'])/$viewDailyDetailItems[0]->toArray()['baseCount'],2);
            }else{
                $dailyUserReadItems[$key]['avgNewsTime'] = 0;
            }

            //查出每日观看视频奖励平均间隔时间并贴到基数中
            $viewDailyDetailItems = (new GoldProductRecord())->getDailyItemDetailUsage(
                $user_id
                ,'usual_read'
                ,$begin_time
                ,$end_time
                ,1
                ,$conditions
            );

            if(!empty($viewDailyDetailItems)){
                $dailyUserReadItems[$key]['avgViedoTime'] = round(($viewDailyDetailItems[0]->toArray()['maxTime'] - $viewDailyDetailItems[0]->toArray()['minTime'])/$viewDailyDetailItems[0]->toArray()['baseCount'],2);
            }else{
                $dailyUserReadItems[$key]['avgViedoTime'] = 0;
            }

            //查出每日进贡奖励平均间隔并贴到基数中
            $viewDailyDetailItems = (new GoldProductRecord())->getDailyItemDetailUsage(
                $user_id
                ,0
                ,$begin_time
                ,$end_time
                ,2
                ,$conditions
            );

            if(!empty($viewDailyDetailItems)){
                $dailyUserReadItems[$key]['avgGivenTime'] = round(($viewDailyDetailItems[0]->toArray()['maxTime'] - $viewDailyDetailItems[0]->toArray()['minTime'])/$viewDailyDetailItems[0]->toArray()['baseCount'],2);
                $dailyUserReadItems[$key]['total_gold'] = ($viewDailyDetailItems[0]->toArray()['given_gold'] + $item->toArray()['gold']);
            }else{
                $dailyUserReadItems[$key]['avgGivenTime'] = 0;
                $dailyUserReadItems[$key]['total_gold'] = 0;
            }
           
        }

        $this->assign('list',$dailyUserReadItems);

        return $this->view->fetch();

    }
   

}
