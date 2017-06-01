<?php

namespace Mll\Server;

use Mll\Core\Factory as DFactory;

class Factory
{
    public static function getInstance($driver = 'Http')
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__ . "\\Driver\\{$driver}";
        return DFactory::getInstance($className);
    }
}