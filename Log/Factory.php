<?php

namespace Mll\Log;

use Mll\Core\Factory as DFactory;

class Factory
{
    public static function getInstance($driver = 'File')
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__."\\Driver\\{$driver}";

        return DFactory::getInstance($className);
    }
}
