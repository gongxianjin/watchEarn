<?php
namespace app\app\controller\mission_new;


use app\app\controller\BaseController;
use app\app\library\Gold;
use app\app\library\GoldRunExt;
use app\app\library\UserTackRecord;
use app\model\GoldRun;
use app\model\UserTaskRecord;
use think\Request;

class NoobQa extends BaseController implements MissionInterface
{

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
        return out([
            ['title'=>'了解淘视界','content'=>'仔细阅读下面的内容，便于您能答对所有题目领取奖励^_^']
            ,['title'=>'什么是淘视界？','content'=>'淘视界是一款致力于促进全民阅读的新形式的资讯阅读软件。为了帮助大家养成良好的阅读习惯，在阅读资讯的过程中，将给予一定的金币奖励，积攒的金币可换算成零钱进行商品兑换或提。让您在阅读中不仅增长见识，还能有所收益。']
            ,['title'=>'淘视界为什么会给用户奖励？','content'=>'或许您还不知道，大家平常阅读新闻资讯这本身就是有价值的行为，只不过在别的阅读平台并没有把您的阅读价值体现出来。而淘视界将千万网友的浏览进行整合变现，并与您一起分享收益，实现用户、平台、媒体的共赢。']
            ,['title'=>'用户如何获取收益？','content'=>'只要您每天登录淘视界进行阅读内容、签到、完成任务、参与我们举办的各项活动，都可以获得一定的金币奖励。另外邀请您的亲朋好友一起玩淘视界，还可以获得额外的现金红包和更多的金币奖励！']
            ,['title'=>'一金币等于多少人民币？','content'=>'淘视界提供的阅读金币来源于广告金主的赞助收入。不同阶段广告金主的赞助额度不一样，发放的金币数也会有一定的区别。每天凌晨，平台会根据赞助收入计算出当日的金币汇率，前日所赚取的金币会在第二天凌晨自动转换为零钱，计入您的账户。']
            ,['title'=>'为什么要收徒？','content'=>'邀请朋友一起来玩淘视界，即收徒弟。收徒能让您的收益保障，除了平台的活动奖励外，徒弟平常的阅读行文，将向您进贡一定的金币，徒弟越多进贡金币也就越多。']
        ]);
    }

    /**
     * 问题列表
     * @return \think\response\Json
     */
    function q()
    {
        return out([
            ['a_ok'=>"A","a"=>"什么是淘视界？","q"=>[['c_key'=>'A','c_val'=>'咨询阅读软件'],['c_key'=>'B','c_val'=>'学习工具'],['c_key'=>'C','c_val'=>'赚钱软件']]],
            ['a_ok'=>"C","a"=>"淘视界为什么给用户发钱？","q"=>[['c_key'=>'A','c_val'=>'有钱任性'],['c_key'=>'B','c_val'=>'宣传噱头'],['c_key'=>'C','c_val'=>'收益分成']]],
            ['a_ok'=>"A","a"=>"金币汇率受什么影响？","q"=>[['c_key'=>'A','c_val'=>'平台收益'],['c_key'=>'B','c_val'=>'老板心情'],['c_key'=>'C','c_val'=>'阅读内容']]],
            ['a_ok'=>"B","a"=>"如何增加收益？","q"=>[['c_key'=>'A','c_val'=>'囤积金币'],['c_key'=>'B','c_val'=>'收取徒弟'],['c_key'=>'C','c_val'=>'好好学习']]],
            ['a_ok'=>"C","a"=>"使用中遇到问题怎么办？","q"=>[['c_key'=>'A','c_val'=>'我不知道'],['c_key'=>'B','c_val'=>'大声呼叫'],['c_key'=>'C','c_val'=>'查看常见问题']]],
            ['a_ok'=>"C","a"=>"邀请好友有什么好处？","q"=>[['c_key'=>'A','c_val'=>'为了炫耀'],['c_key'=>'B','c_val'=>'不知道'],['c_key'=>'C','c_val'=>'收益暴涨']]],
        ]);
    }

    /**
     * 用户回答问题完成后
     *
     * @return array|mixed
     *
     * @throws
     */
    function handler()
    {

        $userInfo = &$this->userInfo;

        $goldRun = &$this->goldRun;
        if ( $goldRun['is_activation'] == 0 )
            return out("", "10002", "活动已关闭");

        $isWatchTutorial = (new UserTaskRecord)->once($userInfo['c_user_id'],$goldRun['key_code']);
        if (!empty($isWatchTutorial))
            return out("","10002","已参与回答");

        //匿名执行
        $func = function () use
        (
            &$userInfo
            ,&$goldRun
        )
        {
            UserTackRecord::createRecord($userInfo['c_user_id'],$goldRun['key_code']);
            (new GoldRunExt())->addUserGoldExtAll([
                'user_id'=>$userInfo['c_user_id']
                ,'task_id'=>$goldRun['id']
                ,'hours_at'=>0
                ,'key_code'=>$goldRun['key_code']
                ,'type'=>$goldRun['type']
                ,'status'=>1
                ,'num'=>1
                ,'sycle'=>0
                ,'gold_tribute'=>$goldRun['gold_flag']
                ,'father_gold_tribute'=>0
                ,'grandfather_gold_tribute'=>0
                ,'is_del'=>1
                ,'description'=>'完成新人问答任务'
            ],$goldRun);
        };

        //入库
        $gold = new Gold();

        $goldTribute = $goldRun['gold_flag'];
        $status = 1;
        $type = $goldRun['id'];
        $typeKey = $goldRun['key_code'];
        $title = '完成答题任务';
        //默认数据
        $goldData = [
            'user_id'=>$userInfo['c_user_id']
            ,'gold_tribute'=>$goldTribute
            ,'status'=>$status
            ,'type'=>$type
            ,'type_key'=>$typeKey
            ,'title'=>$title
            ,'func'=>$func
        ];
        try
        {
            $gold->addUserGold($goldData);
        }
        catch (\Exception $e)
        {
            return out("", "10002", $e->getMessage());
        }

        return out(null,'200','验证完成，快去开红包吧');
    }

}
