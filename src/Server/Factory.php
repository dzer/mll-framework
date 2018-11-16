<?php

namespace Mll\Server;

use Mll\Core\Container;

/**
 * 工厂方法
 *
 * @package Mll\Server
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Factory
{
    public static function getInstance($driver = 'Http')
    {
        $className = __NAMESPACE__ . "\\Driver\\{$driver}";
        return Container::getInstance($className);
    }
}