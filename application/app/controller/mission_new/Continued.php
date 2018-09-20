<?php
namespace app\app\controller\mission_new;


use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use app\model\FConfig;
use app\model\GoldProductRecord;
use app\model\GoldRun;
use app\model\Grade;
use app\model\User;

class Continued extends BaseController implements MissionInterface
{

    private static $SEARCH_KEY_CODE = [
        'last_use'
    ];

    const LAST_USE_KEY = 'last_use';

    /**
     * @var GoldRun
     */
    protected $goldRun = null;

    function _initGoldRun(&$goldRun)
    {
        $this->goldRun = $goldRun;
    }

    /**
     * 基础信息
     * @return \think\response\Json
     */
    function info()
    {
        return out();
    }

    /**
     * 用户达到使用半小时之后
     *
     * @return array|mixed
     *
     * @throws
     */
    function handler()
    {
        //默认值
        $userInfo = &$this->userInfo;
        $goldRun = &$this->goldRun;

        //排号情况缓存
        $userCacheKey = 'cacheLastUse_'.$this->user_id;
        $param = input('post.');
        $f_code = empty($param['f_code']) ? '' : $param['f_code'];
//        cache($userCacheKey,null,1);
        $lastTime = 30 * 60;
        $nextTime = 24 * 3600;

        if(empty($f_code)) {
            //不传入排号码接口
                $cacheUse = cache($userCacheKey);
                if(empty($cacheUse)){
                    //没有排号码情况
                    $code = uuid();
                    $cacheData = json_encode(['code' => $code,'time' => time() + $lastTime,'next' => time() + $nextTime]);
                    cache($userCacheKey,$cacheData,$nextTime * 2);
                    return out(['f_code' => $code,'last' => $lastTime,'reset' => $lastTime],200,'success');
                }else{
                    //有排号码情况
                    $cacheData = json_decode($cacheUse,true);
                    if($cacheData['next'] >= time()){
                        //排号码有效 返回
                        $reset = $cacheData['next']  - time();
                        $reset = $reset < 0 ? 0 : $reset;
                        return out(['f_code' => $cacheData['code'],'last' => $lastTime,'reset' =>  $reset],200,'success');
                    }else{
                        //重新排号码情况
                        $code = uuid();
                        $cacheData = json_encode(['code' => $code,'time' => time() + $lastTime,'next' => time() + $nextTime]);
                        cache($userCacheKey,$cacheData,$nextTime * 2);
                        return out(['f_code' => $code,'last' => $lastTime,'reset' => $lastTime],200,'success');
                    }
                }
        }else{
            //传入排号码接口
            $cacheUse = cache($userCacheKey);
            if(empty($cacheUse)){
                return out('',200,'error');
            }
            $cacheData = json_decode($cacheUse,true);
            if($cacheData['time'] > time()){
                return out('',200,'time error');
            }

            if($cacheData['code'] != $f_code){
                return out('',200,'code error');
            }

            $goldRunExt = new GoldRunExt();

            $data=$goldRunExt->newMissionValidateBaseAll($userInfo['c_user_id'],$goldRun);//验证

            //特权师傅
            $data['father_gold_tribute']=0;
            $father_title=getArrVal($this->userInfo,'nickname') . $goldRun['title'];
            //匿名执行
            $func = function () use ( &$data ,&$goldRun ,&$goldRunExt )
            {
                $goldRunExt->addUserGoldExtAll($data,$goldRun);
            };

            //默认数据
            $goldData = [
                'user_id'=>$data['user_id']
                ,'gold_tribute'=>$data['gold_tribute']
                ,'status'=>2
                ,'type'=>$data['task_id']
                ,'type_key'=>$data['key_code']
                ,'title'=>$goldRun['title']
                ,'type_task_id'=>0
                ,'father_gold_tribute'=> 0
                ,'grandfather_gold_tribute'=>0
                ,'tribute_status'=>2
                ,'tribute_title'=>$father_title
                ,'func'=>$func
            ];

            $gold=Gold::addUserGold($goldData);

            if($gold){
                $cacheData = json_encode(['code' => '','time' => 0,'next' =>  time() + $nextTime]);
                cache($userCacheKey,$cacheData,24 * 3600);
                return out(array('gold_flag'=>$data['gold_tribute']),200,$goldRun['title']);
            }else{
                return out('', 502, '请稍后重试');
            }
        }
    }

}
