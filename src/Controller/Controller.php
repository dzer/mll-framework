<?php

namespace Mll\Controller;

use Mll\Core\Container;
use Mll\Mll;
use Mll\Response\Response;

/**
 * Class Mll
 *
 * @package Mll
 * @property \Mll\Config\Driver\ArrayFormat $config
 * @property \Mll\Request\Base $request
 * @property \Mll\Log\ILog $log
 * @property \Mll\Server\IServer $server
 * @property \Mll\Rpc\IRpc $rpc
 * @property \Mll\Session\Session $session
 * @property \Mll\Cache\ICache $cache
 * @property \Mll\View\Base $view
 * @property \Mll\Curl\Curl $curl
 * @property \Mll\Queue\IQueue $queue
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Controller implements IController
{
    public function beforeAction()
    {
        return true;
        // TODO: Implement beforeAction() method.
    }

    public function afterAction()
    {
        // TODO: Implement afterAction() method.
    }

    public function json($data = [], $code = 200, $header = [], $options = [])
    {
        return Mll::app()->response->create($data, 'json', $code, $header, $options);
    }

    public function render($template, $params = [], $code = 200, $header = [], $options = [])
    {
        $params['IMG'] = Mll::app()->config->get('source_server_host.0');
        $params['RES'] = Mll::app()->config->get('res_server_host');

        $content = Mll::app()->view->fetch($template, $params);
        return Mll::app()->response->create($data, 'html', $code, $header, $options);
    }

    /**
     * 设置模板参数
     *
     * @param string $name 参数名
     * @param string $value 值
     * @return mixed
     */
    public function assign($name, $value = '')
    {
        return Mll::app()->view->assign($name, $value);
    }

    /**
     * 跳转
     *
     * @param string $url 跳转url
     * @return mixed
     */
    public function redirect($url)
    {
        return Mll::app()->response->redirect($url);
    }

    /**
     * 对象池
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return Container::get($name);
    }

    /**
     * 获取request变量.
     *
     * @param string $name 数据名称
     * @param string $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function request($name = '', $default = null, $filter = '')
    {
        return $this->request->request($name, $default, $filter);
    }

    /**
     * 设置获取获取GET参数.
     *
     * @param string|array $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function get($name = '', $default = null, $filter = '')
    {
        return $this->request->get($name, $default, $filter);
    }

    /**
     * 设置获取获取POST参数.
     *
     * @param string $name 变量名
     * @param mixed $default 默认值
     * @param string|array $filter 过滤方法
     *
     * @return mixed
     */
    public function post($name = '', $default = null, $filter = '')
    {
        return $this->request->post($name, $default, $filter);
    }
}
