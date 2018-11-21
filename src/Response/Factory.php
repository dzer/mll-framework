<?php

namespace Mll\Response;

use Mll\Core\Container;

/**
 * 工厂方法
 *
 * @package Mll\Response
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Factory
{
    public static function getInstance()
    {
        $className = __NAMESPACE__ . "\\Response";
        return Container::getInstance($className);
    }
}