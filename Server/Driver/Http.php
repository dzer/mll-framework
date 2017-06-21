<?php

namespace Mll\Server\Driver;

use Mll\Mll;
use Mll\Server\IServer;
use Mll\Core;

class Http implements IServer
{
    public function run()
    {
        //解析url
        Mll::app()->request->parse();
        //加载模块配置文件
        Mll::app()->config->load(Mll::getConfigPath(Mll::app()->request->getModule()));
        return Core\Route::route();
    }
}
