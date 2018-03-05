<?php

namespace Mll\Config\Driver;

use Mll\Common\Dir;
use Mll\Config\IConfig;

/**
 * PHP数组格式配置文件类
 *
 * @package Mll\Config\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class ArrayFormat implements IConfig
{
    /**
     * 配置文件.
     *
     * @var array
     */
    private static $config = [];

    /**
     * 加载配置文件目录
     *
     * @param array $configPathArr 配置文件路径
     *
     * @return array 返回配置文件
     */
    public function load(array $configPathArr)
    {
        $config = array();
        if (is_array($configPathArr)) {
            foreach ($configPathArr as $configPath) {
                $files = Dir::tree($configPath, '/.php$/');
                array_map(function ($file) use (&$config) {
                    $config += include $file;
                }, $files);
            }
        }
        self::$config = array_merge(self::$config, $config);

        return self::$config;
    }

    /**
     * 加载配置文件
     *
     * @param string $configFile 配置文件
     *
     * @return array 返回配置文件
     */
    public function loadFile($configFile)
    {
        if (is_string($configFile) && file_exists($configFile)) {
            $config = include $configFile;
            self::$config = array_merge(self::$config, $config);
        }
        return self::$config;
    }

    /**
     * 获取配置.
     *
     * @param string $key 键名，支持点号获取多维数组，例如 cache.memcache.code
     * @param null $default 默认值，当配置不存在时返回默认值
     * @param bool $throw 不存在时是否抛出错误
     *
     * @return mixed|null 返回相应配置
     *
     * @throws \Exception
     */
    public function get($key, $default = null, $throw = false)
    {
        $key = '["' . implode('"]["', explode('.', $key)) . '"]';
        $varStr = 'self::$config' . $key;
        $result = eval("return isset($varStr) ? $varStr : \$default;");
        if ($throw && is_null($result)) {
            throw new \Exception('{key} config empty');
        }

        return $result;
    }

    /**
     * 设置配置.
     *
     * @param string $key 键名，支持点号设置多维数组，例如 cache.memcache.code
     * @param mixed $value 值
     * @param bool $set 当配置已存在，设置是否覆盖
     *
     * @return bool
     */
    public function set($key, $value, $set = true)
    {
        $key = '["' . implode('"]["', explode('.', $key)) . '"]';
        $varStr = 'self::$config' . $key;
        if ($set) {
            eval("$varStr = \$value;");
        } else {
            if (empty(eval("return $varStr;"))) {
                eval("$varStr = \$value;");
            }
        }

        return true;
    }

    /**
     * 获取参数配置.
     *
     * @param string $key 键名，支持点号获取多维数组，例如 cache.memcache.code
     * @param null $default 默认值，当配置不存在时返回默认值
     * @param bool $throw 不存在时是否抛出错误
     *
     * @return mixed|null
     *
     * @throws \Exception
     */
    public function params($key, $default = null, $throw = false)
    {
        $key = 'params.' . $key;

        return self::get($key, $default, $throw);
    }

    /**
     * 获取所有配置.
     *
     * @return array
     */
    public function all()
    {
        return self::$config;
    }
}
