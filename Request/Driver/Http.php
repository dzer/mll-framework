<?php

namespace Mll\Request\Driver;

use Mll\Core\Config;
use Mll\Request\IRequest;

class Http extends Base implements IRequest
{
    /**
     * 将不同server的传输数据统一格式
     *
     * @param $requestParams
     * @return void
     */
    public static function parse($requestParams)
    {
        self::$_module = Config::getField('main', 'defaultModule', 'Index');
        self::$_module = Config::getField('main', 'defaultController', 'main\\main');
        self::$_module = Config::getField('main', 'defaultMethod', 'main');
        $apn = Config::getField('main', 'ctrl_name', 'a');
        $mpn = Config::getField('main', 'method_name', 'm');
        if (isset($data[$apn])) {
            $ctrlName = \str_replace('/', '\\', $data[$apn]);
        }
        if (isset($data[$mpn])) {
            $methodName = $data[$mpn];
        }

        $pathInfo = Request::getPathInfo();
        if (!empty($pathInfo) && '/' !== $pathInfo) {
            $routeMap = ZRoute::match(Config::get('route', false), $pathInfo);
            if (is_array($routeMap)) {
                $ctrlName = \str_replace('/', '\\', $routeMap[0]);
                $methodName = $routeMap[1];
                if (!empty($routeMap[2]) && is_array($routeMap[2])) {
                    //参数优先
                    $data = $data + $routeMap[2];
                }
            }
        }
    }
}