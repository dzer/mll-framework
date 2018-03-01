<?php

namespace Mll\Service;

use Mll\Core\Container;

/**
 * service
 *
 * @package Mll
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Service
{
    /**
     * 对象池
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return Container::get($name);
    }
}
