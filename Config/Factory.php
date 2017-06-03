<?php

namespace Mll\Config;

use Mll\Core\Container;

class Factory
{
    public static function getInstance($driver = 'ArrayFormat')
    {
        $className = __NAMESPACE__ . "\\Driver\\{$driver}";
        return Container::getInstance($className);
    }
}