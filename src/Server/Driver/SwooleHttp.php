<?php

namespace Mll\Server\Driver;

use Mll\Mll;
use Mll\Server\IServer;
use Mll\Core;

/**
 * Http服务
 *
 * @package Mll\Server\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class SwooleHttp implements IServer
{
    /**
     * 运行服务
     *
     * @return mixed
     */
    public function run()
    {
        //解析url
        Mll::app()->request->parse();
        //加载模块配置文件
        Mll::app()->config->load(Mll::getConfigPath(Mll::app()->request->getModule()));

        return Core\Route::route();
    }
}
