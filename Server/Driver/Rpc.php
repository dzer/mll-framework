<?php

namespace Mll\Server\Driver;

use Mll\Mll;
use Mll\Server\IServer;
use Mll\Core;

class Rpc implements IServer
{
    public function run()
    {
        $rpc = new \Yar_Server(new self());
        $rpc->handle();
    }

    public function api($pathInfo, $params)
    {
        $method = strtolower(isset($params['method']) ? $params['method'] : 'GET');

        if ($method == 'get') {
            $_GET = array_merge($_GET, $params['param']);
        }
        if ($method == 'post') {
            $_POST = array_merge($_POST, $params['param']);
        }
        //解析url
        Mll::app()->request->parse($pathInfo, $params['param']);
        //加载模块配置文件
        Mll::app()->config->load(Mll::getConfigPath(Mll::app()->request->getModule()));

        return Core\Route::route();
    }
}

