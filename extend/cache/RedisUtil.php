<?php

/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 17-8-19
 * Time: 下午8:08
 */
namespace cache;

class RedisUtil
{
    private static $handler = null;
    private static $_instance = null;
    private static $options = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 0,
        'expire' => 0,
        //是否长链接
        'persistent' => false,
        'prefix' => '',
    ];

    private function __construct($options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');      //判断是否有扩展
        }
        if (!empty($options)) {
            self::$options = $options;
        }
        $func = self::$options['persistent'] ? 'pconnect' : 'connect';     //长链接
        self::$handler = new \Redis;
        self::$handler->$func(self::$options['host'], self::$options['port'], self::$options['timeout']);

        if ('' != self::$options['password']) {
            self::$handler->auth(self::$options['password']);
        }

        if (0 != self::$options['select']) {
            self::$handler->select(self::$options['select']);
        }
    }


    /**
     * 返回实例
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self(config('redis'));
        }
        return self::$_instance;
    }

    /**
     * 禁止外部克隆
     */
    public function __clone()
    {
        trigger_error('Clone is not allow!',E_USER_ERROR);
    }

    /**
     * 写入缓存
     * @param string $key 键名
     * @param string $value 键值
     * @param int $exprie 过期时间 0:永不过期
     * @return bool
     */
    public function set($key, $value, $exprie = 0)
    {
        if ($exprie == 0) {
            $set = self::$handler->set($key, $value);
        } else {
            $set = self::$handler->setex($key, $exprie, $value);
        }
        return $set;
    }

    /**
     * 读取缓存
     * @param string $key 键值
     * @return mixed
     */
    public function get($key)
    {
        $fun = is_array($key) ? 'Mget' : 'get';
        return self::$handler->{$fun}($key);
    }

    /**
     * 删除缓存
     * @param string $key 键值
     * @return mixed
     */
    public function del($key)
    {
        return self::$handler->del($key);
    }

    /**
     * 获取值长度
     * @param string $key
     * @return int
     */
    public function lLen($key)
    {
        return self::$handler->lLen($key);
    }

    /**
     * 将一个或多个值插入到列表头部
     * @param $key
     * @param $value
     * @return int
     */
    public function lPush($key, $value)
    {
        return self::$handler->lPush($key, $value);
    }

    /**
     * 移出并获取列表的第一个元素
     * @param string $key
     * @return string
     */
    public function lPop($key)
    {
        return self::$handler->lPop($key);
    }

}