<?php

namespace Mll\Session;

use Mll\Core\Container;

class Factory
{
    public static function getInstance($driver = 'session', $config = [])
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__ . "\\{$driver}";
        return Container::getInstance($className, $config);
    }
}
