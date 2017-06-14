<?php

namespace Mll\Request\Driver;

use Mll\Core\Route;
use Mll\Mll;
use Mll\Request\IRequest;
use Mll\Request\Base;

class Http extends Base implements IRequest
{

    /**
     * 将不同server的传输数据统一格式
     *
     * @param $requestParams
     * @return void
     */
    public function parse($requestParams = null)
    {
        $this->module = $this->config['default_module'];
        $this->controller = $this->config['default_controller'];
        $this->action = $this->config['default_action'];

        $pathInfo = $this->getPathInfo();
        var_dump($pathInfo);
        if (!empty($pathInfo)) {
            list($path, $var) = Route::parseUrlPath($pathInfo);
            var_dump($path);
            var_dump($var);
            die;
        }

        if (!empty($pathInfo) && '/' !== $pathInfo) {
            //路由替换
            $routeMap = Route::match(Mll::app()->config->get('route'), $pathInfo);
            if (is_array($routeMap)) {
                $ctrlName = \str_replace('/', '\\', $routeMap[0]);
                $methodName = $routeMap[1];
                if (!empty($routeMap[2]) && is_array($routeMap[2])) {
                    //参数优先
                    $data = $data + $routeMap[2];
                }
            }
        }
        Request::init($ctrlName, $methodName, $data, Config::getField('project', 'view_mode', 'Php'));
        return true;
    }

    /**
     * getPathInfo
     *
     * @return string
     */
    public function getPathInfo()
    {
        if (isset($_GET[$this->config['path_info_var']])) {
            $pathInfo = $_GET[$this->config['path_info_var']];
            unset($_GET[$this->config['path_info_var']]);
            return $pathInfo;
        }
        return isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    }
}