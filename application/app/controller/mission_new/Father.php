<?php

namespace app\app\controller\mission_new;


use think\Db;

class Father
{

    //邀请徒弟
    private static $INVITED_APPRENTICE_CONFIG_FILE = 'data/invited_apprentice.json';
    private static $INVITED_APPRENTICE = null;

    public function __construct()
    {

    }

    /**
     * 获取倍数
     * @param int $fatherUserId 徒弟数量
     * @return int 倍数
     */
    public static function getMultiple($fatherUserId)
    {
        $result = Db::query('
        SELECT
        (
        select
        multiple
        from
        hs_grade
        where
        id = hs_user.grade_id
        ) as multiple
        FROM
        hs_user
        where
        c_user_id = ?
        limit 1;
',[$fatherUserId]);

        $multiple = empty($result)?1:$result[0]['multiple'];

        return $multiple;
    }


    /**
     * 邀请收徒 徒弟收益
     *
     * @param $registerTime
     * @return int
     */
    public static function getInvitedApprenticeReward($registerTime)
    {
        self::$INVITED_APPRENTICE === null && self::initConfigFile(self::$INVITED_APPRENTICE_CONFIG_FILE,self::$INVITED_APPRENTICE);

        $reward = 0;

        $dayDiff = ceil((time()-$registerTime)/3600/24);

        foreach (self::$INVITED_APPRENTICE['apprentice'] as $k=>$v)
        {
            if ($dayDiff>=$k)
                $reward = $v;
        }
        return $reward;
    }

    /**
     * 邀请收徒 徒孙收益
     *
     * @param $registerTime
     * @return int
     */
    public static function getInvitedSubApprenticeReward($registerTime)
    {
        self::$INVITED_APPRENTICE === null && self::initConfigFile(self::$INVITED_APPRENTICE_CONFIG_FILE,self::$INVITED_APPRENTICE);

        $reward = 0;

        $dayDiff = ceil((time()-$registerTime)/3600/24);

        foreach (self::$INVITED_APPRENTICE['subApprentice'] as $k=>$v)
        {
            if ($dayDiff>=$k)
                $reward = $v;
        }
        return $reward;
    }


    private static function initConfigFile($configFile,&$var)
    {
        $filePath = __DIR__.'/'.$configFile;

        !is_file($filePath) && exit("$configFile not exists");

        $var = json_decode(file_get_contents($filePath),true);
    }


}
