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
        //Mll::app()->request->clear();
        //Mll::app()->response->clear();
        //Core\Container::$instances['Mll\\Request\\Driver\\SwooleHttp'] = null;
        //Core\Container::$instances['Mll\\Response\\Response'] = null;
        $GLOBALS = null;
        $swooleRequest = Mll::app()->swooleRequest;

        $_GET = $swooleRequest->get;
        $_POST = $swooleRequest->post;
        $_COOKIE = $swooleRequest->cookie;
        $_FILES = $swooleRequest->files;
        $_SERVER = array_change_key_case($swooleRequest->server, CASE_UPPER);

        $request = new \Mll\Request\Driver\SwooleHttp(self::$config);
        $response = new Response();
        Mll::app()->request = $request;
        Mll::app()->response = $response;

        $request->header($swooleRequest->header);

        //解析url
        $request->parse();
        //加载模块配置文件
        Mll::app()->config->load(Mll::getConfigPath($request->getModule()));

        return Core\Route::route();
    }
}
