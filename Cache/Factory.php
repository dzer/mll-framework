<?php

namespace Mll\Cache;

use Mll\Core\Container;

class Factory
{
    public static function getInstance($driver = 'cache', $config = [])
    {
        $driver = ucfirst(strtolower($driver));
        $className = "Mll\\{$driver}";
        return Container::getInstance($className, $config);
    }
}
