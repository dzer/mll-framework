<?php

namespace Mll\View;

use Mll\Core\Container;

class Factory
{
    public static function getInstance($adapter = 'Php')
    {
        $adapter = ucfirst(strtolower($adapter));
        $className = __NAMESPACE__ . "\\Driver\\{$adapter}";

        return Container::getInstance($className);
    }
}