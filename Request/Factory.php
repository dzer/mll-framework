<?php

namespace Mll\Request;

use Mll\Core\Container;
use Mll\Core\Factory as DFactory;

class Factory
{
    public static function getInstance($driver = 'Http', $config = [])
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__."\\Driver\\{$driver}";

        return Container::getInstance($className, $config);
    }
}
