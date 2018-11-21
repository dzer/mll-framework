<?php

namespace Mll\Request\Driver;

use Mll\Core\Route;
use Mll\Mll;
use Mll\Request\IRequest;
use Mll\Request\Base;

/**
 * Http请求类.
 *
 * @author Xu Dong <d20053140@gmail.com>
 *
 * @since 1.0
 */
class SwooleHttp extends Base implements IRequest
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * 将不同server的传输数据统一格式.
     *
     * @param string $pathInfo pathInfo
     * @param mixed  $params   参数
     */
    public function parse($pathInfo = null, $params = null)
    {
        // 保存 php://input
        $this->input = Mll::app()->swooleRequest->rawContent();

        //设置请求时间
        $this->setRequestTime(microtime(true));

        $module = $this->config['default_module'];
        $controller = $this->config['default_controller'];
        $action = $this->config['default_action'];
        $pathInfo = $_SERVER['PATH_INFO'];

        if (empty($pathInfo)) {
            $pathInfo = $this->getPathInfo();
        }

        if (!empty($pathInfo)) {
            list($path, $var) = Route::parseUrlPath($pathInfo);
        }

        if (isset($path)) {
            // 解析模块
            $module = !empty($path) ? array_shift($path) : null;
            // 解析控制器
            $controller = !empty($path) ? array_shift($path) : null;
            // 解析操作
            $action = !empty($path) ? array_shift($path) : null;
        }
        // 解析额外参数
        $this->parseUrlParams(empty($path) ? '' : implode('|', $path), $var);
        $data = $this->param();

        $viewMode = Mll::app()->config->get('view_mode', 'Php');

        $this->init($module, $controller, $action, $data, $viewMode);
    }

    /**
     * 初始化request.
     *
     * @param string $module 模块
     * @param string $controller 控制器
     * @param string $action 方法
     * @param array $params 请求参数
     * @param null $viewMode 视图模型
     */
    public function init($module, $controller, $action, array $params, $viewMode = null)
    {
        parent::init($module, $controller, $action, $params, $viewMode);
        go(function(){
            $this->setRequestId();
            $this->setTraceId();
        });
    }
}
