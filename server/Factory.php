<?php

namespace mll\server;

use mll\core\Factory as DFactory;

class Factory
{
    public static function getInstance($driver = 'Http')
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__ . "\\driver\\{$driver}";
        return DFactory::getInstance($className);
    }
}