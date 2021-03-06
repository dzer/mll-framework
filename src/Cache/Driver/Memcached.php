<?php

namespace Mll\Cache\Driver;

use Mll\Cache\Base;
use Mll\Cache\Cache;
use Mll\Cache\ICache;
use Mll\Mll;

/**
 * memcached缓存
 *
 * @package Mll\Cache\Driver
 * @author wang zhou <zhouwang@mll.com>
 * @since 1.0
 */
class Memcached extends Base implements ICache
{
    protected $handler = null;
    protected $options = [
        'host' => '127.0.0.1',
        'port' => 11211,
        'expire' => 0,
        'timeout' => 0, // 超时时间（单位：毫秒）
        'persistent' => true,
        'prefix' => '',
    ];

    /**
     * 架构函数
     *
     * @param array $options 缓存参数
     * @access public
     *
     * @throws \BadFunctionCallException
     */
    public function __construct($options = [])
    {
        if (!extension_loaded('memcached')) {
            throw new \BadFunctionCallException('not support: memcached');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->handler = new \Memcached;
        // 支持集群
        $hosts = explode(',', $this->options['host']);
        $ports = explode(',', $this->options['port']);
        if (empty($ports[0])) {
            $ports[0] = 11211;
        }
        // 建立连接
        foreach ((array)$hosts as $i => $host) {
            $port = isset($ports[$i]) ? $ports[$i] : $ports[0];
            $this->handler->addServer($host, $port, 1);
        }
    }

    public function init($driver, $cacheName = '')
    {
        return Cache::init($driver, $cacheName);
    }

    /**
     * 判断缓存
     *
     * @access public
     * @param string $name 缓存变量名
     *
     * @return bool
     */
    public function has($name)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->get($key) ? true : false;
    }

    /**
     * 读取缓存
     *
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $default 默认值
     *
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $result = $this->handler->get($this->getCacheKey($name));
        return false !== $result ? $result : $default;
    }

    /**
     * 写入缓存
     *
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value 存储数据
     * @param integer $expire 有效时间（秒）
     *
     * @return bool
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if ($this->tag && !$this->has($name)) {
            $first = true;
        }
        $key = $this->getCacheKey($name);
        if ($this->handler->set($key, $value, $expire)) {
            isset($first) && $this->setTagItem($key);
            return true;
        }
        return false;
    }

    /**
     * 自增缓存（针对数值缓存）
     *
     * @access public
     * @param string $name 缓存变量名
     * @param int $step 步长
     *
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->increment($key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     *
     * @access public
     * @param string $name 缓存变量名
     * @param int $step 步长
     *
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        $key = $this->getCacheKey($name);
        $value = $this->handler->get($key) - $step;
        $res = $this->handler->set($key, $value);
        if (!$res) {
            return false;
        } else {
            return $value;
        }
    }

    /**
     * 删除缓存
     *
     * @param string $name 缓存变量名
     * @param integer $time 服务端等待删除该元素的总时间
     *
     * @return bool
     */
    public function rm($name, $time = 0)
    {
        $key = $this->getCacheKey($name);
        return $this->handler->delete($key, $time);
    }

    /**
     * 清除缓存
     *
     * @access public
     * @param string $tag 标签名
     *
     * @return bool
     */
    public function clear($tag = null)
    {
        if ($tag) {
            // 指定标签清除
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                $this->handler->delete($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        }
        return $this->handler->flush();
    }
}
