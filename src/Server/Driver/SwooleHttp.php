<?php

namespace Mll\Server\Driver;

use Mll\Mll;
use Mll\Response\Response;
use Mll\Server\IServer;
use Mll\Core;

/**
 * Http服务
 *
 * @package Mll\Server\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class SwooleHttp
{
    private static $config;

    public function __construct()
    {
        self::$config = Mll::app()->config->get('request.swoolehttp');
    }

    /**
     * 运行服务
     *
     * @return mixed
     */
    public function run()
    {
        ob_start();
        $GLOBALS = null;
        $swooleRequest = Mll::app()->swooleRequest;
        $request = new \Mll\Request\Driver\SwooleHttp(self::$config);
        $response = new Response();
        Mll::app()->request = $request;
        Mll::app()->response = $response;

        Mll::app()->request->get($swooleRequest->get);
        Mll::app()->request->post($swooleRequest->post);
        Mll::app()->request->cookie($swooleRequest->cookie);
        Mll::app()->request->files($swooleRequest->files);
        Mll::app()->request->server($swooleRequest->server);
        Mll::app()->request->header($swooleRequest->header);

        $request->header($swooleRequest->header);

        //解析url
        $request->parse();
        //加载模块配置文件
        Mll::app()->config->load(Mll::getConfigPath($request->getModule()));

        return Core\Route::route();
    }
}
