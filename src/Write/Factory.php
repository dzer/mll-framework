<?php

namespace Mll\Write;

use Mll\Core\Container;

/**
 * 缓存工厂类
 *
 * @package Mll\Cache
 * @author wang zhou <zhouwang@mll.com>
 * @since 1.0
 */
class Factory
{
    public static function getInstance($driver = 'write', $config = [])
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__ . $driver;
        return Container::getInstance($className, $config);
    }
}
