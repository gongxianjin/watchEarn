<?php
namespace app\model;

use think\Model;

class ShareVisit extends Model
{
    protected $updateTime = false;
    /**
     * 数据统计
     */
    static public function visited_count($user_id,$activity_type,$code,$share_channel){
        $data =self::where(["user_id"=>$user_id,"activity_type"=>$activity_type,'share_channel'=>$share_channel])
        				->whereTime("addtime","today")
        				->find();

        if($data){
            $update['addtime']= time();
            if($code == 200){
                $update['success_hits'] = ['exp',"success_hits+1"];
            }
            if($code == -1){//失败
                $update['error_hits'] = ['exp',"error_hits+1"];
            }
            if($code == -2){//取消
                $update['cancel_hits'] = ['exp',"cancel_hits+1"];
            }
            self::where(['id'=>$data['id']])->update($update);
        }else{
            $add['user_id'] = $user_id;
            $add['activity_type']=$activity_type;
            $add['addtime']=time();
            $add['share_channel']=$share_channel;
            if($code == 200){
                $add['success_hits'] =1;
            }
            if($code == -1){//失败
                $add['error_hits'] =1;
            }
            if($code == -2){//取消
                $add['cancel_hits'] =1;
            }
            self::insert($add);
        }
    }

}
