<?php

namespace Mll\Queue;

use Mll\Core\Container;
use Mll\Mll;

/**
 * Class Factory
 *
 * @package Mll\Queue
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Factory
{
    public static function getInstance($driver = 'redis', $config = [])
    {
        $driver = ucfirst(strtolower($driver));
        $className = __NAMESPACE__ . "\\Driver\\{$driver}";
        if (empty($config)) {
            $config = Mll::app()->config->get('queue.' . strtolower($driver), []);
        }
        return Container::getInstance($className, $config);
    }
}
