<?php

namespace Mll\Log;

use Mll\Core\Container;

/**
 * 工厂类
 *
 * @package Mll\Log
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Factory
{
    public static function getInstance($driver = 'File', $config = [])
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__."\\Driver\\{$driver}";

        return Container::getInstance($className, $config);
    }
}
