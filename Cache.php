<?php

namespace Mll;

/**
 * cache类
 *
 * @package Mll
 * @author wang zhou <zhouwang@mll.com>
 * @since 1.0
 */
class Cache
{
    protected static $instance = [];
    public static $readTimes = 0;
    public static $writeTimes = 0;
    public static $options = [];
    /**
     * 操作句柄.
     *
     * @var object
     */
    protected static $handler;

    /**
     * 连接缓存.
     *
     * @param array $options 配置数组
     * @param bool|string $name 缓存连接标识 true 强制重新连接
     * @param string $driver 驱动名
     *
     * @return \Mll\Cache
     */
    public static function connect(array $options = [], $name = false, $driver = '')
    {
        if (false === $name) {
            $name = md5(serialize($options));
        }

        if (true === $name || !isset(self::$instance[$name])) {
            $class = false !== strpos($driver, '\\') ? $driver : '\\Mll\\Cache\\Driver\\' . ucwords($driver);

            // 记录初始化信息 todo
            if (true === $name) {
                return new $class($options);
            } else {
                self::$instance[$name] = new $class($options);
            }
        }
        self::$handler = self::$instance[$name];

        return self::$handler;
    }

    /**
     * 自动初始化缓存.
     *
     * @param array $options 配置数组
     */
    public static function init(array $options = [])
    {
        if (is_null(self::$handler)) {
            // 自动初始化缓存
            if (!empty($options)) {
                self::connect($options);
            } else {
                self::cut('default');
            }
        }
    }

    /**
     * 切换缓存类型 需要配置 cache.type 为 complex.
     *
     * @param string $name 缓存标识 memcache.code
     *
     * @return \Mll\Cache\Base
     *
     * @throws \ErrorException
     */
    public static function cut($name)
    {
        if (empty($name)) {
            throw new \ErrorException('cache : cut params error!');
        }
        if (isset(self::$instance[$name]) && is_object($name)) {
            self::$handler = self::$instance[$name];
            return self::$handler;
        }
        self::loadOptions();
        if (!isset(self::$options[$name]['driver']) || empty(self::$options[$name]['driver'])) {
            throw new \ErrorException('cache : driver configuration error!');
        }
        self::connect(
            self::$options[$name],
            $name,
            self::$options[$name]['driver']
        );
        return self::$handler;
    }
    /**
     * 加载缓存配置.
     *
     * @return array
     *
     * @throws \ErrorException
     */
    private static function loadOptions(){
        self::$options = Mll::app()->config->get('cache');
        if(empty(self::$options)){
            throw new \ErrorException('cache : no configuration found!');
        }
    }

    /**
     * 判断缓存是否存在.
     *
     * @param string $name 缓存变量名
     *
     * @return bool
     */
    public static function has($name)
    {
        self::init();
        ++self::$readTimes;

        return self::$handler->has($name);
    }

    /**
     * 读取缓存.
     *
     * @param string $name 缓存标识
     * @param mixed $default 默认值
     *
     * @return mixed
     */
    public static function get($name, $default = false)
    {
        self::init();
        ++self::$readTimes;

        return self::$handler->get($name, $default);
    }

    /**
     * 写入缓存.
     *
     * @param string $name 缓存标识
     * @param mixed $value 存储数据
     * @param int|null $expire 有效时间 0为永久
     *
     * @return bool
     */
    public static function set($name, $value, $expire = null)
    {
        self::init();
        ++self::$writeTimes;

        return self::$handler->set($name, $value, $expire);
    }

    /**
     * 自增缓存（针对数值缓存）.
     *
     * @param string $name 缓存变量名
     * @param int $step 步长
     *
     * @return false|int
     */
    public static function inc($name, $step = 1)
    {
        self::init();
        ++self::$writeTimes;

        return self::$handler->inc($name, $step);
    }

    /**
     * 自减缓存（针对数值缓存）.
     *
     * @param string $name 缓存变量名
     * @param int $step 步长
     *
     * @return false|int
     */
    public static function dec($name, $step = 1)
    {
        self::init();
        ++self::$writeTimes;

        return self::$handler->dec($name, $step);
    }

    /**
     * 删除缓存.
     *
     * @param string $name 缓存标识
     *
     * @return bool
     */
    public static function rm($name)
    {
        self::init();
        ++self::$writeTimes;

        return self::$handler->rm($name);
    }

    /**
     * 清除缓存.
     *
     * @param string $tag 标签名
     *
     * @return bool
     */
    public static function clear($tag = null)
    {
        self::init();
        ++self::$writeTimes;

        return self::$handler->clear($tag);
    }

    /**
     * 缓存标签.
     *
     * @param string $name 标签名
     * @param string|array $keys 缓存标识
     * @param bool $overlay 是否覆盖
     *
     * @return \Mll\Cache\Base
     */
    public static function tag($name, $keys = null, $overlay = false)
    {
        self::init();

        return self::$handler->tag($name, $keys, $overlay);
    }
}
