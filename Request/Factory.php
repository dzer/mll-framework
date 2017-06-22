<?php

namespace Mll\Request;

use Mll\Core\Container;
use Mll\Core\Factory as DFactory;

/**
 * 工厂类
 *
 * @package Mll\Request
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Factory
{
    public static function getInstance($driver = 'Http', $config = [])
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__."\\Driver\\{$driver}";

        return Container::getInstance($className, $config);
    }
}
