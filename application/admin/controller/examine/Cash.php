<?php

namespace app\admin\controller\examine;

use app\common\controller\Backend;
use app\model\BalanceLog;
use app\model\User;
use think\Controller;
use think\Request;
use think\Db;
//use app\model\Video;
use app\model\UserCashRecord;
use Payment\WxCash;

/**
 * 微信配置管理
 *
 * @icon fa fa-circle-o
 */
class Cash extends Backend
{

    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new UserCashRecord();
    }

    /**
     * 查看
     */
  public function index()
{
    if ($this->request->isAjax())
    {
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        //$where['state'] =['in',[0]];
        //$where['type'] =2;
        //$where['examine_status'] =1;
        $map = "state in (0) AND  type = 2 AND  examine_status = 1";
        $total = $this->model
                ->where($where)
                ->where($map)
                ->order($sort, $order)
                ->count();

        $list = $this->model
                ->where($where)
                ->where($map)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
        if(!empty($list)){
            if(!empty($list)){
                foreach ($list as $key => &$value) {
                    $value['id'] = strval($value['id']);
                    //$value['create_time'] = date('Y-m-d H:i',$value['create_time']);
                    $value['details_url'] = "/admin/user/user/index?c_user_id=".$value['user_id'];
                    $value['details_son_url'] = "/admin/user/apprentice/distinctsum?c_user_id=".$value['user_id'];
                }
            }
        }

        $result = array("total" => $total, "rows" => $list);
        return json($result);
    }
    return $this->view->fetch();
}
    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            //pp($params);die;

            if ($params)
            {
                foreach ($params as $k => &$v)
                {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }
                try
                {
                    //是否采用模型验证
                    if ($this->modelValidate)
                    {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $this->model->validate($validate);
                    }
                    $result = $this->model->save($params);

                    if ($result !== false)
                    {
                        $this->success();
                    }
                    else
                    {
                        $this->error($this->model->getError());
                    }
                }
                catch (\think\exception\PDOException $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {

        $row = $this->model->get($ids);
       
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            if ($params)
            {
                foreach ($params as $k => &$v)
                {
                    $v = is_array($v) ? implode(',', $v) : $v;
                }

                if (isset($params['mode'] ) && $params['mode'] == 'json')
                {
                    //JSON字段
                    $fieldarr = $valuearr = [];
                    $field = $this->request->post('field/a');
                    $value = $this->request->post('value/a');
                    foreach ($field as $k => $v)
                    {
                        if ($v != '')
                        {
                            $fieldarr[] = $field[$k];
                            $valuearr[] = $value[$k];
                        }
                    }
                    $params['value'] = json_encode(array_combine($fieldarr, $valuearr), JSON_UNESCAPED_UNICODE);
                }
                unset($params['mode']);
                try
                {
                    //是否采用模型验证
                    if ($this->modelValidate)
                    {
                        $name = basename(str_replace('\\', '/', get_class($this->model)));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : true) : $this->modelValidate;
                        $row->validate($validate);
                    }
                    if($params['status'] == 2){
                        $params['dis_time'] = time();
                    }
                    $result = $row->save($params);
                    if ($result !== false)
                    {
                        $this->success();
                    }
                    else
                    {
                        $this->error($row->getError());
                    }
                }
                catch (think\exception\PDOException $e)
                {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    /**
     * 用户金币来源详情
     * @param  模型，引用传递
     * @param  查询条件
     * @param int  每页查询条数
     * @return 返回
     */
    public function examine($ids = NULL){
        $row = $this->model->get($ids);
       if($row['type']!=2 || $row['examine_status'] != 1 || $row['state'] > 0){
             $this->error("错误请求");
        }

        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            //var_dump($params);die;
            $examine_status = $params['examine_status'];
            $reason = $params["reason"];
            if(empty($examine_status)){
                 $this->error("审核状态不能为空");
            }
            $up=[
              
                "examine_status"=>$examine_status,   
                "reason"=>$reason,
            ];
            $userModel = new User();
            $user = $userModel->where(['c_user_id' => $row['user_id']])->find();
            if(empty($user)){
                $this->error("提现账户信息有误！");
            }
            //事务操作
            Db::startTrans();
            switch ($examine_status) {
                //通过
                case 2:
                    $up['state'] = 1;
                    //减去用户冻结金额
                    $res = Db::name("user")->where(['c_user_id' => $row['user_id']])->update(['frozen_balance' => ['exp', "frozen_balance-" . $row['amount']]]);
                    if (!$res) {
                        Db::rollback();
                        $this->error("审核失败", "");
                    }
                    break;

                //不通过
                case 3:
                    if (empty($reason)) {
                        Db::rollback();
                        $this->error("请输入不通过原因");
                    }
                    //更新参数
                    $up['state'] = 2;

                    $frozen_balance = $user->frozen_balance - $row['amount'] < 0 ? 0 : $user->frozen_balance - $row['amount'];
                    $user->frozen_balance = $frozen_balance;
                    //减去用户金额
                    $res = $user->save();
                    if (!$res) {
                        Db::rollback();
                        $this->error("审核失败", "");
                    }
                    break;

                //驳回
                case 4:
                    if (empty($reason)) {
                        Db::rollback();
                        $this->error("请输入不通过原因");
                    }
                    //更新参数
                    $up['state'] = 2;

                    $frozen_balance = $user->frozen_balance - $row['amount'];
                    if($frozen_balance < 0){
                        Db::rollback();
                        $this->error("账户冻结金额不足，请详细查看用户账户记录");
                    }

                    $user->frozen_balance = $frozen_balance;
                    $user->balance = $user->balance + $row['amount'];


                    //添加账户流水
                    $balanceLog = [
                        'user_id' => $row['user_id'],
                        'balance' => $row['amount'],
                        'title' => 'Reject',
                        'create_time' => time(),
                        'type' => 3
                    ];

                    $banlanceModel = new BalanceLog();
                    $res = $banlanceModel->insert($balanceLog);
                    if (!$res) {
                        Db::rollback();
                        $this->error("驳回失败", "");
                    }
                    //返还提现金额
                    $res = $user->save();
                    if (!$res) {
                        Db::rollback();
                        $this->error("审核失败", "");
                    }
                    break;
            }
            $up['examine_time'] = time();
            $result = $row->save($up);
            if ($result !== false)
            {
                Db::commit();
                $this->success("审核成功","");
            }
            else
            {
                Db::rollback();
                $this->error("审核失败","");
            }
           // $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    

}
