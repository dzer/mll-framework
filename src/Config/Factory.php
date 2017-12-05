<?php

namespace Mll\Config;

use Mll\Core\Container;

/**
 * 工厂类
 *
 * @package Mll\Config
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Factory
{
    /**
     * 获取实例
     *
     * @param string $driver
     * @return mixed
     */
    public static function getInstance($driver = 'ArrayFormat')
    {
        $className = __NAMESPACE__ . "\\Driver\\{$driver}";
        return Container::getInstance($className);
    }
}