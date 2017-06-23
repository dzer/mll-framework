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
    private static $cutParamsCount = 2;
    /**
     * 操作句柄.
     *
     * @var object
     */
    protected static $handler;

    public function __construct($options = [])
    {
        self::$options = $options;
    }

    /**
     * 连接缓存.
     *
     * @param array $options 配置数组
     * @param bool|string $name 缓存连接标识 true 强制重新连接
     * @param string $driver 驱动名
     *
     * @return \Mll\Cache
     */
    public function connect(array $options = [], $name = false, $driver = '')
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
    public function init(array $options = [])
    {
        if (is_null(self::$handler)) {
            // 自动初始化缓存
            if (!empty($options)) {
                $this->connect($options);
            } else {
                $this->cut('default.default');
            }
        }
    }

    /**
     * 切换缓存类型 需要配置 cache.type 为 complex.
     *
     * @param string $name 缓存标识 memcache.code
     *
     * @return \Mll\Cache\Base
     */
    public function cut($name)
    {
        $split = explode('.', $name);
        $split = array_filter($split);
        if (self::$cutParamsCount != count($split)) {
            throw new \ErrorException('cache : switch identification error!');
        }
        $cacheType = $split[0];
        $cacheItem = $split[1];
        $alias = strtolower($cacheType . '_' . $cacheItem);
        if (isset(self::$instance[$alias]) && is_object($alias)) {
            self::$handler = self::$instance[$alias];

            return self::$handler;
        }
        if (!isset(self::$options[$cacheType]['driver']) || empty(self::$options[$cacheType]['driver'])) {
            throw new \ErrorException('cache : driver configuration error!');
        }
        if (!isset(self::$options[$cacheType][$cacheItem]) || empty(self::$options[$cacheType][$cacheItem])) {
            throw new \ErrorException('cache : no configuration found!');
        }
        $this->connect(
            self::$options[$cacheType][$cacheItem],
            $alias,
            self::$options[$cacheType]['driver']
        );

        return self::$handler;
    }

    /**
     * 判断缓存是否存在.
     *
     * @param string $name 缓存变量名
     *
     * @return bool
     */
    public function has($name)
    {
        $this->init();
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
    public function get($name, $default = false)
    {
        $this->init();
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
    public function set($name, $value, $expire = null)
    {
        $this->init();
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
    public function inc($name, $step = 1)
    {
        $this->init();
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
    public function dec($name, $step = 1)
    {
        $this->init();
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
    public function rm($name)
    {
        $this->init();
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
    public function clear($tag = null)
    {
        $this->init();
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
    public function tag($name, $keys = null, $overlay = false)
    {
        $this->init();

        return self::$handler->tag($name, $keys, $overlay);
    }
}
