<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\model\UserCashRecord;
use think\Controller;
use think\Request;
use app\model\User as UserModel;

/**
 * 微信配置管理
 *
 * @icon fa fa-circle-o
 */
class Apprentice extends Backend
{

    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new UserModel();
    }
    /**
     * 查看
     */
   public function index($ids = null,$grandsonids = null,$meid = null,$passwd = null,$type = null,$ip = null,$level = null,$leveltype=null)
    {
        $map = [];
        $ids = input("ids");
        $grandsonids = input("grandsonids");
        $meid = input("meid");
        $passwd = input("passwd");
        $type = input("type");
        $ip = input("ip");
        $level = input("level");
        $leveltype = input("leveltype");
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $ids = input("ids");
            if($ids){
                $map['user_father_id']=$ids;
            }
            if($ip){
                $map['user_ip']=$ip;
                $map['c_user_id'] = ['neq',$ids];
                unset($map['user_father_id']);
            }
            if($grandsonids){
                $map['user_grandfather_id']=$grandsonids;
            }
            if($meid){
                $map['meid']=$meid;
            }
            if($passwd){
                $map['login_passwd']=$passwd;
            }
            if($type){
                $findNums = '';

                //1.生成子查询：查出所有徒弟meid相同的数量
                $sonMeidTogethers = $this->model  //设置数据表
                    -> where($map)
                    -> group($type)
                    -> having('count(1) > 1')
                    -> order($type.' DESC')
                    -> field($type)
                    -> select();    //false表示不执行查询，仅返回SQL语句
                if(!empty($sonMeidTogethers)){
                    foreach ($sonMeidTogethers as $item){
                        $findNums .= '\''.$item[$type].'\''.',';
                    }
                    $findNums = rtrim($findNums,',');
                    //2.执行父查询：查出徒弟meid与徒孙meid相同个数
                    $total = $this->model
                        ->where($where)
                        ->where($map)
                        ->where($type.' in '.'('.$findNums.')')
                        ->order($type, $order)
                        ->count();

                    $list = $this->model
                        ->where($where)
                        ->where($map)
                        ->where($type.' in '.'('.$findNums.')')
                        ->order($type, $order)
                        ->limit($offset, $limit)
                        ->select();
                }else{
                    return false;
                }

            }elseif($level){

                if(1 == $leveltype){
                    //1.生成子查询：查出所有徒孙meid
                    $songrandTogethers = $this->model  //设置数据表
                    -> where(['user_grandfather_id'=>$ids])
                        -> group($level)
                        -> field($level)
                        -> select(false);    //false表示不执行查询，仅返回SQL语句

                    $total = $this->model
                        ->where($where)
                        ->where($map)
                        ->where('('.$level.') IN '.'('.$songrandTogethers.')')
                        ->order($sort, $order)
                        ->limit($offset, $limit)
                        ->count();

                    //2.执行父查询：查出徒弟meid与徒孙meid相同个数
                    $listGrandSonInSonLeverl = $this->model
                        ->where($where)
                        ->where($map)
                        ->where('('.$level.') IN '.'('.$songrandTogethers.')')
                        ->order($sort, $order)
                        ->limit($offset, $limit)
                        ->select();

                    $list = $listGrandSonInSonLeverl;

                }else{
                    $total =  (new UserModel())->FindSomeItemCount($ids,$level,$map,$where,$sort,$order);

                    //1.生成子查询：查出所有徒孙meid
                    $songrandTogethers = $this->model  //设置数据表
                    -> where(['user_grandfather_id'=>$ids])
                        -> group($level)
                        -> field($level)
                        -> select(false);    //false表示不执行查询，仅返回SQL语句

                    //2.执行父查询：查出徒弟meid与徒孙meid相同个数
                    $listGrandSonInSonLeverl = $this->model
                        ->where($where)
                        ->where($map)
                        ->where('('.$level.') IN '.'('.$songrandTogethers.')')
                        ->order($sort, $order)
                        ->limit($offset, $limit)
                        ->select();

                    //1.生成子查询：查出所有徒孙meid
                    $sonTogethers = $this->model  //设置数据表
                    -> where($map)
                        -> group($level)
                        -> field($level)
                        -> select(false);    //false表示不执行查询，仅返回SQL语句

                    //2.执行父查询：查出徒弟meid与徒孙meid相同个数
                    $listSonInGrandSonLeverl = $this->model
                        ->where($where)
                        ->where(['user_grandfather_id'=>$ids])
                        ->where('('.$level.') IN '.'('.$sonTogethers.')')
                        ->order($sort, $order)
                        ->limit($offset, $limit)
                        ->select();

                    $list = array_merge($listGrandSonInSonLeverl,$listSonInGrandSonLeverl);
                }


            }else{

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

            }
            if(!empty($list)){
                if(!empty($list)){

                }
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->assign("ids",$ids?$ids:0);
        $this->assign("grandsonids",$grandsonids?$grandsonids:0);
        $this->assign("meid",$meid?$meid:0);
        $this->assign("passwd",$passwd?$passwd:0);
        $this->assign("type",$type?$type:0);
        $this->assign("ip",$ip?$ip:0);
        $this->assign("level",$level?$level:0);
        $this->assign("leveltype",$leveltype?$leveltype:0);

        return $this->view->fetch();
    }

    /**
     * 统计
     */
    public function distinctSum($ids = null)
    {
        //查出userid,根据uid找到所有子节点.
        $UserCashRecordModel = new UserCashRecord();
        $UserCashRecord = $UserCashRecordModel->getOrderRcordByid($ids);
        if (!$UserCashRecord)
            $this->error(__('No Results were found'));
        $user_id = $UserCashRecord['user_id'];
        $userInfo = $this->model->where(['c_user_id'=>$user_id])->find();

        $this->assign('user_id',$user_id);
        $this->assign('meid',$userInfo['meid']);
        $this->assign('passwd',$userInfo['login_passwd']);
        $this->assign('ip',$userInfo['user_ip']);

        $userModel =  new UserModel();

        //查找当前用户的总徒弟量
        $mapson=['user_father_id'=>$user_id];
        $sontotals = $userModel->getCountByValue($mapson);
        $this->assign('sontotals',$sontotals);

        //查找当前用户的总徒孙量
        $mapgrandson=['user_grandfather_id'=>$user_id];
        $grandsontotals = $userModel->getCountByValue($mapgrandson);
        $this->assign('grandsontotals',$grandsontotals);

        //查找当前用户的徒弟跟自己meid相同的个数
        $mapMeid['user_father_id'] = $user_id;
        $mapMeid['meid'] = $userInfo['meid'];
        $sonMeidTotals = $userModel->getCountByValue($mapMeid);
        $this->assign('sonMeidTotals',$sonMeidTotals);

        //查找当前用户的徒弟跟自己密码相同的个数
        $mapPasswd['user_father_id'] = $user_id;
        $mapPasswd['login_passwd'] = $userInfo['login_passwd'];
        $sonPasswdTotals = $userModel->getCountByValue($mapPasswd);
        $this->assign('sonPasswdTotals',$sonPasswdTotals);

        //查找当前用户的徒弟之间meid相同的个数

        $allsoncounts = $this->model  //设置数据表
            -> where(['user_father_id'=>$user_id])
            -> count();

        //1.生成子查询：查出所有徒弟meid相同数大于1的
        $onlymeidsoncounts = $this->model  //设置数据表
                 -> where(['user_father_id'=>$user_id])
                 -> group('meid')
                 -> having('count(1) = 1')
                 -> field('id,meid,count(1) as count')
                 -> count();    //false表示不执行查询，仅返回SQL语句

        $this->assign('sonTogetherMeidTotals',$sontotals - $onlymeidsoncounts);

        //查找当前用户的徒弟之间密码相同的个数

        //1.生成子查询：查出所有徒弟meid相同数大于1的
        $onlyPasswdsoncounts = $this->model  //设置数据表
            -> where(['user_father_id'=>$user_id])
            -> group('login_passwd')
            -> having('count(1) = 1')
            -> field('id,login_passwd,count(1) as count')
            -> count();    //false表示不执行查询，仅返回SQL语句
        $this->assign('sonTogetherPasswdTotals',$sontotals - $onlyPasswdsoncounts);


        //查找徒孙之间的meid相同个数
        $onlyMeidgrandsoncounts = $this->model  //设置数据表
        -> where(['user_grandfather_id'=>$user_id])
            -> group('meid')
            -> having('count(1) = 1')
            -> field('id,meid,count(1) as count')
            -> count();    //false表示不执行查询，仅返回SQL语句
        $this->assign('songrandTogetherMeidTotals',$grandsontotals - $onlyMeidgrandsoncounts);


        //查找徒孙之间的密码相同个数
        $onlyPasswdgrandsoncounts = $this->model  //设置数据表
        -> where(['user_grandfather_id'=>$user_id])
            -> group('login_passwd')
            -> having('count(1) = 1')
            -> field('id,login_passwd,count(1) as count')
            -> count();    //false表示不执行查询，仅返回SQL语句

        $this->assign('subgrandPasswdTogethers',$grandsontotals - $onlyPasswdgrandsoncounts);

        //查找同一ip是否有多个用户
        $mapip['user_ip'] = $userInfo['user_ip'];
        $ipTotals = $userModel->getCountByValue($mapip);
        $ipTotals = $ipTotals - 1;
        $this->assign('ipTotals',$ipTotals);

        //查找徒弟和徒孙之间meid相同个数
        
        //1.生成子查询：查出所有徒孙meid
        $songrandMeidTogethers = $this->model  //设置数据表
        -> where(['user_grandfather_id'=>$user_id])
            -> group('meid')
            -> field('meid')
            -> select(false);    //false表示不执行查询，仅返回SQL语句
        //2.执行父查询：查出徒弟meid与徒孙meid相同个数
        $GrandsonInsonMeids = $this->model
                    ->where(['user_father_id'=>$user_id])
                    ->where('meid in '.'('.$songrandMeidTogethers.')')
                    ->count();
        //1.生成子查询：查出所有徒孙meid
        $sonMeidTogethers = $this->model  //设置数据表
        -> where(['user_father_id'=>$user_id])
            -> group('meid')
            -> field('meid')
            -> select(false);    //false表示不执行查询，仅返回SQL语句

        $SonInGrandSonMeids = $this->model
            ->where(['user_grandfather_id'=>$user_id])
            ->where('meid in '.'('.$sonMeidTogethers.')')
            ->count();

        $sonAndGrandsonMeids = $SonInGrandSonMeids + $GrandsonInsonMeids;

        $this->assign('sonAndGrandsonMeids',$sonAndGrandsonMeids);

        //查找徒弟和徒孙之间密码相同个数

        //1.生成子查询：查出所有徒孙密码
        $sonPasswdTogethers = $this->model  //设置数据表
        -> where(['user_father_id'=>$user_id])
            -> group('login_passwd')
            -> field('login_passwd')
            -> select(false);    //false表示不执行查询，仅返回SQL语句

        //2.执行父查询：查出徒弟密码与徒孙密码相同个数
        $sonInGrandsonPasswds = $this->model
            ->where(['user_grandfather_id'=>$user_id])
            ->where('login_passwd in '.'('.$sonPasswdTogethers.')')
            ->count();


        //1.生成子查询：查出所有徒孙密码
        $songrandPasswdTogethers = $this->model  //设置数据表
        -> where(['user_grandfather_id'=>$user_id])
            -> group('login_passwd')
            -> field('login_passwd')
            -> select(false);    //false表示不执行查询，仅返回SQL语句

        //2.执行父查询：查出徒弟密码与徒孙密码相同个数
        $grandsonInSonPasswds = $this->model
            ->where(['user_father_id'=>$user_id])
            ->where('login_passwd in '.'('.$songrandPasswdTogethers.')')
            ->count();

        $sonAndGrandsonPasswds =  $sonInGrandsonPasswds + $grandsonInSonPasswds;

        $this->assign('sonAndGrandsonPasswds',$sonAndGrandsonPasswds);

        return $this->view->fetch();
    }

   

}
