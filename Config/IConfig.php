<?php

namespace Mll\Config;

interface IConfig
{
    /**
     * 加载配置文件
     *
     * @param array $configDir 配置文件目录,z
     *
     * @return mixed
     */
    public function load(array $configDir);

    /**
     * 获取配置项
     *
     * @param string $key 键名，支持点号，例如cache.mll.host
     * @param null $default 默认值
     * @param bool $throw
     * @return mixed
     */
    public function get($key, $default = null, $throw = false);

    /**
     * 设置配置项
     *
     * @param string $key 键名，支持点号，例如cache.mll.host
     * @param mixed $value 设置值
     * @param bool $set 设置为false时配置项非空才设置
     * @return mixed
     */
    public function set($key, $value, $set = true);

    /**
     * 获取所有配置项
     *
     * @return mixed
     */
    public function all();


}
