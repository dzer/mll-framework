<?php

namespace Mll\Rpc;

use Mll\Core\Container;

class Factory
{
    public static function getInstance($driver = 'Yar', $config = [])
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__."\\Driver\\{$driver}";

        return Container::getInstance($className, $config);
    }
}