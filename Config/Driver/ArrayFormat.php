<?php

namespace Mll\Config\Driver;

use Mll\Common\Dir;
use Mll\Config\IConfig;

class ArrayFormat implements IConfig
{

    /**
     * 配置文件
     * @var array
     */
    private static $config;


    /**
     * load.
     *
     * @param $configPathArr
     *
     * @return array
     */
    public static function load(array $configPathArr)
    {
        $config = array();
        if (is_array($configPathArr)) {
            foreach ($configPathArr as $configPath) {
                $files = Dir::tree($configPath, '/.php$/');
                array_map(function ($file) use (&$config) {
                    $config += include "{$file}";
                }, $files);
            }
        }
        self::$config = $config;

        return self::$config;
    }

    /**
     * 合并配置文件
     *
     * @param $file
     * @return bool
     */
    public static function mergeFile($file)
    {
        $tmp = include "{$file}";
        if (empty($tmp)) {
            return false;
        }
        self::$config = array_merge(self::$config, $tmp);
        return true;
    }


    /**
     * 获取配置
     *
     * @param $key
     * @param null $default
     * @param bool $throw
     * @return mixed|null
     * @throws \Exception
     */
    public static function get($key, $default = null, $throw = false)
    {
        $key = '["' . implode('"]["', explode('.', $key)) . '"]';
        $varStr = 'self::$config' . $key;
        $result = eval("return isset($varStr) ? $varStr : \$default;");
        if ($throw && is_null($result)) {
            throw new \Exception("{key} config empty");
        }
        return $result;
    }

    /**
     * 设置配置
     *
     * @param $key
     * @param $value
     * @param bool $set
     * @return bool
     */
    public static function set($key, $value, $set = true)
    {
        $key = '["' . implode('"]["', explode('.', $key)) . '"]';
        $varStr = 'self::$config' . $key;
        if ($set) {
            eval("$varStr = \$value");
        } else {
            if (empty(eval("$varStr"))) {
                eval("$varStr = \$value");
            }
        }

        return true;
    }

    /**
     * 获取配置
     *
     * @param $key
     * @param null $default
     * @param bool $throw
     * @return mixed|null
     * @throws \Exception
     */
    public static function params($key, $default = null, $throw = false)
    {
        $key = 'params.' . $key;
        return self::get($key, $default, $throw);
    }

    /**
     * 获取所有配置
     *
     * @return array
     */
    public static function all()
    {
        return self::$config;
    }
}
