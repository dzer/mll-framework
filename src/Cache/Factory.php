<?php

namespace Mll\Cache;

use Mll\Core\Container;

/**
 * 缓存工厂类
 *
 * @package Mll\Cache
 * @author wang zhou <zhouwang@mll.com>
 * @since 1.0
 */
class Factory
{
    public static function getInstance($driver = 'memcached')
    {
        return Cache::init($driver);
    }
}
