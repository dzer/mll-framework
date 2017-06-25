<?php

namespace Mll\Server\Driver;

use Mll\Mll;
use Mll\Server\IServer;
use Mll\Core;

/**
 * Rpc服务
 *
 * @package Mll\Server\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Rpc implements IServer
{
    /**
     * 运行服务
     */
    public function run()
    {
        $rpc = new \Yar_Server(new self());
        $rpc->handle();
    }

    /**
     * api
     *
     * @param string $pathInfo PathInfo
     * @param array $params 参数
     * @return mixed
     */
    public function api($pathInfo, $params)
    {
        //去掉yar post自定义的协议
        array_pop($_POST);

        $method = strtolower(isset($params['method']) ? $params['method'] : 'GET');
        if ($method == 'get') {
            $_GET = array_merge($_GET, $params['param']);
        }
        if ($method == 'post') {
            $_POST = array_merge($_POST, $params['param']);
        }
        $_REQUEST = array_merge($_GET, $_POST);

        $request_id_key = Mll::app()->config->get('request.request_id_key', 'x-request-id');
        Mll::app()->request->setRequestId(isset($params[$request_id_key]) ? $params[$request_id_key] : null);
        Mll::app()->request->parse($pathInfo, $params['param']);
        Mll::app()->config->load(Mll::getConfigPath(Mll::app()->request->getModule()));

        return Core\Route::route();
    }
}

