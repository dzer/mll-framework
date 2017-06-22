<?php

namespace Mll\Rpc\Driver;

use Mll\Rpc\IRpc;

/**
 * Yar类
 *
 * @package Mll\Rpc\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Yar implements IRpc
{
    private $config = [
        'host' => '',   //Rpc Host
        'connect_timeout' => 2,    //连接超时
        'timeout' => 10,    //响应超时
    ];

    /**
     * 客户端实例
     * @var
     */
    protected $client;

    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }

        $this->client = new \Yar_Client($this->config['host']);
        $this->client->SetOpt(YAR_OPT_TIMEOUT, $this->config['timeout']);
        $this->client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, $this->config['connect_timeout']);
    }

    /**
     * 模拟get方式请求
     *
     * @param string $url 请求url,例如:goods/Index/index
     * @param array $param 参数
     * @param int $timeout 超时
     *
     * @return mixed 返回Rpc结果
     */
    public function get($url, $param = array(), $timeout = null)
    {
        return $this->api($url, 'GET', $param, $timeout);
    }

    /**
     * 模拟post方式请求
     *
     * @param string $url 请求url,例如:goods/Index/index
     * @param array $param 参数
     * @param int $timeout 超时
     *
     * @return mixed 返回Rpc结果
     */
    public function post($url, $param = array(), $timeout = null)
    {
        return $this->api($url, 'POST', $param, $timeout);
    }

    /**
     * rpc请求方法
     *
     * @param string $url 请求url,例如:goods/Index/index
     * @param string $method 请求方式
     * @param array $param 参数
     * @param int $timeout 超时
     *
     * @return mixed 返回Rpc结果
     */
    protected function api($url, $method, $param, $timeout)
    {
        if ($timeout > 0) {
            $this->client->SetOpt(YAR_OPT_TIMEOUT, (int)$timeout);
        }
        $params = [
            'method' => $method,
            'param' => $param,
        ];
        return $this->client->api($url, $params);
    }

}