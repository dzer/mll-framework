<?php

namespace Mll\Server;

use Mll\Core\Container;

class Factory
{
    public static function getInstance($driver = 'Http')
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__ . "\\Driver\\{$driver}";
        return Container::getInstance($className);
    }
}