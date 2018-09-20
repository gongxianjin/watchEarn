<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 17-12-15
 * Time: 下午11:22
 */

namespace app\app\library;

use cache\RedisUtil;

class Util
{
    public static function decryptToken($token)
    {
        $token_data_json = private_key_decrypt($token);
        if ($token_data_array = json_decode($token_data_json, true)){
            if ($token_data_array['time'] + config('auto_login_time') > time()){
                if($redisToken = RedisUtil::getInstance()->get('userToken'.$token_data_array['user_id'])){
                    if ($redisToken == $token){
                        return ['user_id' => $token_data_array['user_id'], 'app_type' => $token_data_array['app']];
                    }
                }
            }
        }

        return false;
    }
}