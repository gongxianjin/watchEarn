<?php
namespace app\app\controller;


use app\common\service\RecommendVideo;
use app\common\service\UserVideoService;
use app\model\CommentVideo;
use app\model\Domain;
use app\model\FConfig;
use app\model\GoldProductRecord;
use app\model\UserCollection;
use app\model\UserLike;
use app\model\NewVideo as VideoModel;
use think\Exception;
use think\Request;
use think\Db;
use app\model\CommentUpRecords;
use app\model\AdUser;
use app\model\AdSource;
use think\Validate;
use app\common\service\UserVideoLike;
use app\common\service\UserService;

class Video extends BaseController
{

    private static $SEARCH_KEY_CODE = [
        'usual_news_read'
        ,'read_push_messages'
        ,'usual_read'
        ,'red_read'
        ,'high_quality_review_awards'
        ,'hot_search_reward'
    ];

    public function rand()
    {

        $keywords = input("keywords/s","");
        if(!empty($keywords)){
            $result = VideoModel::search($keywords);
        }else{
            $result = VideoModel::rand(); 
        }
        foreach ($result as &$item)
        {
            $item = $item->toArray();
            $item['top_comments'] = [];
        }

        $videoIds = array_column($result,'id');
        $videoComments = (new CommentVideo())->geTopCommentsList($videoIds);

        $tempVideoComments = [];
        $upMsg = [];
        if(!empty($videoComments)){

            $idList = [0];
            //array_column($videoComments, 'id');
            foreach ($videoComments as $k => $v) {
                $idList[]=$v['id'];
            }
            $CommentUpRecords = new CommentUpRecords();
            $upMsg = $CommentUpRecords->where(['comment_id'=>['in',$idList],'user_id'=>$this->user_id,'type'=>1])->column("user_id","comment_id");
        }

        foreach ($videoComments as &$item)
        {
            $item = $item->toArray();
            if(isset($upMsg[$item['id']])){
                $item['is_up'] = true;//已赞
            }else{
                $item['is_up'] = false;//未赞
            }
            if($item['user_id'] == $this->user_id){
                $item['is_sure'] = true;//是
            }else{
                $item['is_sure'] = false; //不是
            }
            !isset($tempVideoComments[$item['video_id']]) && $tempVideoComments[$item['video_id']] = [];
            $tempVideoComments[$item['video_id']][] = $item;
        }

        $videoCommentIds = array_keys($tempVideoComments);

        //查询阅读次数
        $count = 0;
        $countS = (new GoldProductRecord())->getDailyUsage(
            $this->userInfo['c_user_id']
            ,self::$SEARCH_KEY_CODE
            ,mktime(0,0,0)
            ,mktime(24,0,0)
        );

        if (!empty($countS))
            $count = $countS[0]->toArray()['count'];

        $configs=[
            'gold_count'=>0
            ,'redpack_count'=>0
            ,'ad_count'=>0
        ];
        //满足30次则不会在有金币，红包
        if ($count<30)
        {
            $configs = FConfig::query('
        (SELECT value,\'redpack_count\' `key` FROM hs_f_config where name=\'redpack_count\')
UNION ALL
(SELECT value,\'ad_count\' `key` FROM hs_f_config where name=\'ad_count\')
UNION ALL
(SELECT value,\'gold_count\' `key` FROM hs_f_config where name=\'gold_count\')
        ');
            foreach ($configs as $index=>$config)
            {
                $configs[$config['key']] = $config;
                unset($configs[$index]);
            }
        }

        $goldCount= $configs['gold_count']['value'];
        $redpackCount = $configs['redpack_count']['value'];

        foreach ($result as &$item)
        {
            if (in_array($item['id'],$videoCommentIds))
                $item['top_comments'] = $tempVideoComments[$item['id']];

            try
            {
                $item['create_time'] = date("Y-m-d H:i:s",$item['create_time']);
            }
            catch (\Exception $e)
            {
                  $item['create_time'] =date('Y-m-d H:i:s',time());
            }
            $item['is_gold'] = 0;
            $item['is_redpack'] = 0;
            if (rand(0,1) && $goldCount>0) {
                $item['is_gold'] = 1;
                $goldCount--;
            }elseif (rand(0,1) && $redpackCount>0) {
                $item['is_redpack'] = 1;
                $redpackCount--;
            }

        }

        $adCount= $configs['ad_count']['value'];
        for ($i=count($result);$i>0;$i--)
        {
            if (rand(0,1) && $adCount>0)
            {
                array_splice($result,$i,0,[ ['is_ad'=>1] ]);
                $adCount--;
            }

        }

        return out($result);
    }

    public function lists()
    {
        $r_type = input('r_type',0);

        if ($r_type == 0)
        {
            return out([],10001,'r_type is error');
        }
        $page = input('page',1);
        $count = 20;
        $recommendList = [];
        if($r_type != 2){
            $service = new UserVideoService();
            $recommendList = $service->getRecommend($r_type,1,2,'rand()');
            if(is_array($recommendList)){
                $count = 20 - count($recommendList);
            }
        }

        $keywords = input("keywords/s","");
        if(!empty($keywords)){
            $result = VideoModel::searchPages($page,$count,$keywords);
        }else{
            $result = VideoModel::pages($r_type,$page,$count,$this->userInfo);
        }

        if($r_type != 2) {
            if (!empty($recommendList)) {
                $result = array_merge($result, $recommendList);
            }
        }

        shuffle($result);
        if(empty($result)){
            $result= [
              'data'=>[]
              ,'is_has_more'=>false
            ];
            return out($result);
        }

        //添加 like_count 用户点赞数量 62
        foreach ($result as $key => $val){
            $result[$key]['like_count'] = $val['like_count'] + 62;
        }

        //软随机
        for ($i=count($result)-1;$i>0;$i--)
        {
            $from = rand(0,$i);
            $to = rand(0,$i);
            $temp = $result[$to];
            $result[$to] = $result[$from];
            $result[$from] = $temp;
        }
        $videoIds = array_column($result,'id');
        $videoComments = (new CommentVideo())->geTopCommentsList($videoIds);

        $tempVideoComments = [];
        $upMsg = [];
        if(!empty($videoComments)){

            $idList = [0];
            //array_column($videoComments, 'id');
            foreach ($videoComments as $k => $v) {
                $idList[]=$v['id'];
            }
            $CommentUpRecords = new CommentUpRecords();
            $upMsg = $CommentUpRecords->where(['comment_id'=>['in',$idList],'user_id'=>$this->user_id,'type'=>1])->column("user_id","comment_id");
        }

        foreach ($videoComments as &$item)
        {
            $item = $item->toArray();
            if(isset($upMsg[$item['id']])){
                $item['is_up'] = true;//已赞
            }else{
                $item['is_up'] = false;//未赞
            }
            if($item['user_id'] == $this->user_id){
                $item['is_sure'] = true;//是
            }else{
                $item['is_sure'] = false; //不是
            }
            !isset($tempVideoComments[$item['video_id']]) && $tempVideoComments[$item['video_id']] = [];
            $tempVideoComments[$item['video_id']][] = $item;
        }

        $videoCommentIds = array_keys($tempVideoComments);

        //查询阅读次数
        $readCount = 0;
        $countS = (new GoldProductRecord())->getDailyUsage(
            $this->userInfo['c_user_id']
            ,self::$SEARCH_KEY_CODE
            ,mktime(0,0,0)
            ,mktime(24,0,0)
        );

        if (!empty($countS))
            $readCount = $countS[0]->toArray()['count'];

        $configs=[
            'gold_count'=>0
            ,'redpack_count'=>0
            ,'ad_count'=>0
        ];
        //查询配置需要数据
        $configs = FConfig::getVideolistMsg();

        //满足30次则不会在有金币，红包
        if ($readCount >= $configs['day_read_get_count'])
        {
            $configs['gold_count'] = 0;
            $configs['redpack_count'] = 0;
        }else{
            //存在,计算数量 生成的金币+红包 小于 配置数量（后续处理）


        }
        $goldCount= $configs['gold_count'];//阅读+金币
        $redpackCount = $configs['redpack_count'];//阅读红包+金币
        $adCount= $configs['ad_count']; //广告数量

        $videoStopSecond = FConfig::where('name','eq','video_stop_second')->value('value');

        foreach ($result as $key => $item)
        {
            $result[$key]['title'] = empty($item['title']) ? '' : $item['title'];
            $result[$key]['video_watch_second'] = $videoStopSecond*1000;

            if (in_array($result[$key]['id'],$videoCommentIds)){
                $result[$key]['top_comments'] = $tempVideoComments[$item['id']];
            }else{
                $result[$key]['top_comments'] = [];
            }
//            $result[$key]['create_time'] = date("Y-m-d H:i:s",$item['create_time']);
            $result[$key]['is_gold'] = 0;
            $result[$key]['is_redpack'] = 0;
            $result[$key]['is_ad'] = 0;
            $result[$key]['open_browser'] = 0;

            if ($key % 3 === 1 && $goldCount > 0) {
                $result[$key]['is_gold'] = 1;
                $goldCount--;
            }

            //后台每页金币数量无效  金币视频 每隔一个有一个
            /**
            if (rand(0, 1) && $goldCount > 0) {
                $item['is_gold'] = 1;
                $goldCount--;
            } elseif (rand(0, 1) && $redpackCount > 0) {
                $item['is_redpack'] = 1;
                $redpackCount--;
            }
            */
            $result[$key]['ad_otherMsg'] = [
                'imp' => [],
                'clk' => [],
            ];


            $result[$key]["ad_type"]="";
        }
        $pushKey =array_flip(array_keys($result['0']));
        $pushKey['top_comments'] = [];
        $pushKey['r_type'] = intval($r_type);

        if ($configs['ad_display_model'] == 1) {
            //指定位置展示广告
            $adPosition = json_decode($configs['ad_position'], true);

            if (is_array($adPosition) && !empty($adPosition) && empty($keywords)) {
                foreach ($adPosition as $k => $v) {
                    if (isset($result[$k])) {
                        $pushKey['is_ad'] = 1;
                        $pushKey['ad_otherMsg'] = [
                            'imp' => [],
                            'clk' => [],
                        ];
                        $pushKey['ad_type'] = $v;
                        array_splice($result, $k, 0, [$pushKey]);
                    }
                }
            }
        } else {
            $adPosition = json_decode($configs['ad_position'], true);

            //随机展示
            foreach ($result as $l => $d) {
                if (rand(1, floor($count / count($adPosition))) == 1  && $adCount > 0) {
                    $pushKey['is_ad'] = 1;
                    $pushKey['ad_otherMsg'] = [
                        'imp' => [],
                        'clk' => [],
                    ];
                    $pushKey['ad_type'] = $adPosition[array_rand($adPosition)];
                    array_splice($result, $l, 0, [$pushKey]);
                    $adCount--;
                }
            }
        }


        $result= [
          'data'=>$result
          ,'is_has_more'=>count($result)>=$count?true:false
        ];
        return out($result);
    }



    public function detail()
    {
        $userInfo = &$this->userInfo;
        $request = Request::instance()->param();
        $id = input("id/s","");
        if(empty($id)){
            return out("",10001,"错误请求");
        }
        $video = VideoModel::find($id);

        $result = VideoModel::rand(5);
        foreach ($result as &$item)
        {
            $item = $item->toArray();
        }

        if ($video===null)
            return out([],10002,'无视频');

        $video = $video->toArray();

        //点赞书+62
        $video['like_count'] = $video['like_count'] + 62;

        // 视频停留时长
        $videoStopSecond = FConfig::where('name','eq','video_stop_second')->value('value');

        $video['video_watch_second'] = $videoStopSecond * 1000;

        // liked
        $video['is_liked'] = UserLike
                ::where('c_user_id','eq',$userInfo['c_user_id'])
                ->where('a_v_id','eq',$id)
                ->where('c_type','eq',UserLike::VIDEO_TYPE)
                ->find()==true;

        // collected
        $video['is_collected'] = UserCollection
                ::where('c_user_id','eq',$userInfo['c_user_id'])
                ->where('a_v_id','eq',$id)
                ->where('c_type','eq',UserCollection::VIDEO_TYPE)
            ->find()==true;



        //查询阅读次数
        $count = 0;
        $countS = (new GoldProductRecord())->getDailyUsage(
            $this->userInfo['c_user_id']
            ,self::$SEARCH_KEY_CODE
            ,mktime(0,0,0)
            ,mktime(24,0,0)
        );

        if (!empty($countS))
            $count = $countS[0]->toArray()['count'];

        //查询配置需要数据
        $configs = FConfig::getVideolistMsg();

        //满足 day_read_get_count 次则不会在有金币，红包
        if ($count<$configs['day_read_get_count'])
        {
            // config
            $configsTemp = FConfig::query('
        (SELECT value,name as `key` FROM hs_f_config where name=\'redpack_count\')
UNION
(SELECT value,name as `key` FROM hs_f_config where name=\'video_detail_ad_number\')
UNION
(SELECT value,name as `key` FROM hs_f_config where name=\'gold_count\')
        ');
            $configs = [];
            foreach ($configsTemp as $index=>$config)
            {
                $configs[$config['key']] = $config['value'];
                unset($configsTemp[$index]);
            }
        }

        $goldCount= $configs['gold_count'];

        foreach ($result as &$item)
        {
            $item['is_gold'] = 0;
            $item['is_redpack'] = 0;
            $item['top_comments'] = [];
            $item['is_ad'] = 0;
            $item['open_browser'] = 0;
            if (rand(0,1) && $goldCount>0) {
                $item['is_gold'] = 1;
                $goldCount--;
            }
        }

        $pushKey =array_flip(array_keys($result['0']));
        $pushKey['top_comments'] = [];
//        $adCount= $configs['video_detail_ad_number'];
        $adCount = 0;
        if($adCount>0){
            $adUser = AdUser::getAdUser();
            $adMsg  = AdSource::getSource($adCount,false);

        }

        for ($i=count($result);$i>=0;$i--)
        {
            $result[$i]['video_watch_second'] =  $videoStopSecond * 1000;
            if (rand(0,2) && $adCount>0 && !empty($adMsg))
            {
                $adUserkeys=array_rand($adUser,1);
                $adSourcekeys=array_rand($adMsg,1);
                $pushKey['is_ad'] = 1;
                $pushKey['title'] = $adMsg[$adSourcekeys]['title'];
                $pushKey['video_url'] = $adMsg[$adSourcekeys]['ad_url'];
                if(!ckeckHttp($adMsg[$adSourcekeys]['img']['path'])){
                    $pushKey['video_cover']=config("ad_domain").$adMsg[$adSourcekeys]['img']['path'];
                }else{
                    $pushKey['video_cover']=$adMsg[$adSourcekeys]['img']['path'];
                }
                $pushKey['video_height']=$adMsg[$adSourcekeys]['img']['height'];
                $pushKey['video_width']=$adMsg[$adSourcekeys]['img']['width'];
                 $pushKey['video_duration']=0;
                $pushKey['play_count']=getrand();
                $pushKey['user_nickname'] =$adUser[$adUserkeys]['name'];
                $pushKey['user_avatar'] = $adUser[$adUserkeys]['headpic'];
                $pushKey['open_browser'] =  $adMsg[$adSourcekeys]['open_browser'];;//0 视频播放 1 普通浏览器   2 微信
                array_splice($result,$i,0,[$pushKey]);
                $adCount--;
                //删除数据
                unset($adUser[$adUserkeys]);
                unset($adMsg[$adSourcekeys]);
            }

        }

        $result = [
            'video'=>$video
            ,'recommend'=>$result
        ];

        return out($result);
    }


    /**
     * 视频点赞
     * du_type 虚拟或者真实数据源
     * video_id 视频id
     * user_id 用户ID
     */
    public function like(Request $request){

        $params = $request->param();

        $validate = new Validate([
            'du_type'=>'require|number',
            'video_id'=>'require'
        ],
            [
                'type_id.require'=>'required parameter missing',
                'video_id.require'=>'video ID missing',
            ]);

        if(!$validate->check($params)){
            return out([],10001,$validate->getError());
        }else{

            if(!in_array($params['du_type'], [1,2])){
                return out([],10001,"Data error of [du_type]");
            }

            //查询视频是否存在
            $model = new UserVideoService();
            $info = $model->getVideoInfo($params['du_type'],$params['video_id']);

            $user_get_like_res = true;

            if($info){

                $tag_model = new UserVideoLike();
                $user_model = new UserService();
                //查询是否存在点赞记录
                $tag_where = ['c_user_id'=>$this->user_id,'du_type'=>$params['du_type'],"a_v_id"=>$params['video_id'],"c_type"=>UserLike::VIDEO_TYPE];

                $tags_res = $tag_model->userVideoTags($tag_where);


                if($tags_res){//修改点赞记录 取消点赞like_count-1 重新点赞+1

                    $edit_data = [];
                    $count_data = [];
                    $user_get_like = [];
                    $user_like_count = [];
                    if($tags_res['status'] == 1){

                        $edit_data['status'] = 2;
                        $count_data['like_count'] = ['exp',"like_count-1"];

                        $user_get_like['get_like'] = ['exp',"get_like-1"];
                        $user_like_count['like_count'] = ['exp',"like_count-1"];
                    }else if($tags_res['status'] == 2){
                        $edit_data['status'] = 1;
                        $count_data['like_count'] = ['exp',"like_count+1"];

                        $user_get_like['get_like'] = ['exp',"get_like+1"];
                        $user_like_count['like_count'] = ['exp',"like_count+1"];
                    }

                    Db::startTrans();

                    try{

                        $edit_res = $tag_model->editVideoTags(['c_user_id'=>$tags_res['c_user_id'],'a_v_id'=>$tags_res['a_v_id']],$edit_data);

                        $count_res = $model->editVideoLikeCount($params['du_type'],$params['video_id'],$count_data);


                        if($params['du_type'] == 1){
                            //获赞数
                            $user_get_like_res = $user_model->editUserInfo(['c_user_id'=>$info['user_id']],$user_get_like,$params['du_type']);
                        }

                        //点赞数
                        $user_like_count_res = $user_model->editUserInfo(['c_user_id'=>$this->user_id],$user_like_count,$params['du_type']);

                        if($edit_res !== false && $count_res !== false && $user_like_count_res !== false && $user_get_like_res !== false){

                            Db::commit();
                            $res = true;
                        }else{

                            Db::rollback();
                            $res = false;
                        }

                    }catch (\Exception $e){

                        Db::rollback();
                        $res = false;
                    }

                }else{//点赞记录不存在 新增一条

                    Db::startTrans();

                    try{

                        $save_data = [];
                        $save_data['c_user_id'] = $this->user_id;
                        $save_data['a_v_id'] = (string)$params['video_id'];
                        $save_data['c_type'] = UserLike::VIDEO_TYPE;
                        $save_data['du_type'] = $params['du_type'];
                        $save_data['status'] = 1;
                        $save_data['create_time'] = time();

                        $add_res = $tag_model->addVideoTags($save_data);


                        $count_res = $model->editVideoLikeCount($params['du_type'],$params['video_id'],['like_count'=>['exp',"like_count+1"]]);

                        if($params['du_type'] == 1){//真实源视频 才有获赞
                            //获赞数
                            $user_get_like_res = $user_model->editUserInfo(['c_user_id'=>$info['user_id']],['get_like'=>['exp',"get_like+1"]],$params['du_type']);
                        }
                        //点赞数
                        $user_like_count_res = $user_model->editUserInfo(['c_user_id'=>$this->user_id],['like_count'=>['exp',"like_count+1"]],$params['du_type']);

                        if($add_res && $count_res !== false && $user_like_count_res !== false && $user_get_like_res !== false){

                            Db::commit();
                            $res = true;
                        }else{

                            Db::rollback();
                            $res = false;
                        }

                    }catch(\Exception $e){
                        Db::rollback();
                        $res = false;
                    }
                }

                if($res){
                    return out([],200,"Successful operation");
                }else{
                    return out([],10009,"Thumb up failure");
                }

            }else{

                return out([],10002,"Video doesn't exist");
            }


        }

    }

    //获取播放地址
    function aliyunUrl()
    {
        $params =  $this->params;
        $videoId = isset($params['id']) ? $params['id'] : '';
        if(empty($videoId)){
            return out([],200,'video is error');
        }

        $service = new UserVideoService();
        $res = $service->getVideoUrl($videoId);

        if($res['code'] == 0){
            return out($res['data'],200,$res['msg']);
        }

        return out([],$res['code'],$res['msg']);
    }

}
