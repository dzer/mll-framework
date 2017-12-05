<?php

namespace Mll\Rpc;

use Mll\Core\Container;
use Mll\Mll;

/**
 * 工厂方法
 *
 * @package Mll\Rpc
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Factory
{
    public static function getInstance($driver = 'Yar', $config = [])
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__."\\Driver\\{$driver}";
        if (empty($config)) {
            $config = Mll::app()->config->get('rpc.' . strtolower($driver));
        }
        return Container::getInstance($className, $config);
    }
}