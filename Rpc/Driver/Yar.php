<?php

namespace Mll\Rpc\Driver;

use Mll\Rpc\IRpc;

class Yar implements IRpc
{
    private $config = [
        'host' => 'http://mllphp.com/rpc.php',
        'time_out' => 10,
    ];

    /**
     * 客户端实例
     * @var
     */
    protected $client;

    // 实例化并传入参数
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }

        $this->client = new \Yar_Client("http://mllphp.com/rpc.php");
        $this->client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, $this->config['time_out']);
    }

    public function get($url, $param = array(), $timeOut = null)
    {
        return $this->api($url, 'GET', $param, $timeOut);
    }

    public function post($url, $param = array(), $timeOut = null)
    {
        return $this->api($url, 'POST', $param, $timeOut);
    }

    protected function api($url, $method, $param, $timeOut)
    {
        if ($timeOut > 0) {
            $this->client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, (int)$timeOut);
        }
        $params = [
            'method' => $method,
            'param' => $param,
        ];
        return $this->client->api($url, $params);
    }

}