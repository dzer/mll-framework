<?php

namespace Mll\Cache;

use Mll\Core\Container;
use Mll\Mll;

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
     * @var \Mll\Cache\ICache
     */
    protected static $handler;

    /**
     * 初始化缓存
     *
     * @param string $driver 缓存类型
     * @param string $cacheName 缓存host名称
     *
     * @return \Memcached | object
     *
     * @throws \ErrorException
     */
    public static function init($driver, $cacheName = '')
    {
        if (empty($driver)) {
            throw new \ErrorException('cache : cut params error!');
        }

        if (empty(self::$options)) {
            self::loadOptions();
        }

        if (empty($cacheName)) {
            $cacheName = self::$options[$driver]['default'] ?? '';
        }

        $name = $driver . '_' . $cacheName;

        if (isset(self::$instance[$name]) && is_object(self::$instance[$name])) {
            self::$handler = self::$instance[$name];
            return self::$handler;
        }
        if (!isset(self::$options[$driver]) || empty(self::$options[$driver])) {
            throw new \ErrorException('cache : driver configuration error!');
        }

        self::connect(
            self::$options[$driver][$cacheName],
            $name,
            $driver
        );

        return self::$handler;
    }

    /**
     * 连接缓存.
     *
     * @param array $options 配置数组
     * @param bool|string $name 缓存连接标识 true 强制重新连接
     * @param string $driver 驱动名
     *
     * @return \Mll\Cache\Cache
     */
    public static function connect(array $options = [], $name = false, $driver = '')
    {
        if (false === $name) {
            $name = md5(serialize($options));
        }

        if (true === $name || !isset(self::$instance[$name])) {
            $class = false !== strpos($driver, '\\') ? $driver : 'Mll\\Cache\\Driver\\' . ucwords($driver);
            // 记录初始化信息 todo
            if (true === $name) {
                return Container::getInstance($class, $options, true);
            } else {
                self::$instance[$name] = Container::getInstance($class, $options, true);
            }
        }
        self::$handler = self::$instance[$name];
        Container::setAlias('cache', self::$handler);

        return self::$handler;
    }


    /**
     * 加载缓存配置.
     *
     * @throws \ErrorException
     */
    private static function loadOptions()
    {
        self::$options = Mll::app()->config->get('cache');
        if (empty(self::$options)) {
            throw new \ErrorException('cache : no configuration found!');
        }
    }
}
