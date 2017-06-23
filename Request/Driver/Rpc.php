<?php

namespace Mll\Request\Driver;

use Mll\Core\Route;
use Mll\Mll;
use Mll\Request\IRequest;
use Mll\Request\Base;

/**
 * Rpc请求类.
 *
 * @author Xu Dong <d20053140@gmail.com>
 *
 * @since 1.0
 */
class Rpc extends Base implements IRequest
{
    /**
     * 将不同server的传输数据统一格式.
     *
     * @param string $pathInfo pathInfo
     * @param mixed  $params   参数
     */
    public function parse($pathInfo = null, $params = null)
    {
        $module = $this->config['default_module'];
        $controller = $this->config['default_controller'];
        $action = $this->config['default_action'];
        $var = array();
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
}
