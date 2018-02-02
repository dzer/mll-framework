<?php

namespace Mll\Queue\Driver;

use Mll\Queue\IQueue;

/**
 * Class Redis
 *
 * @package Mll\Queue\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Redis implements IQueue
{
    /** @var  \Redis */
    public $handler;

    protected $config = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 0,
        'expire' => 0,
        'persistent' => false,
        'prefix' => '',
    ];

    /**
     * Redis constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
        $func = $this->config['persistent'] ? 'pconnect' : 'connect';
        $this->handler = new \Redis;
        $this->handler->$func($this->config['host'], $this->config['port'], $this->config['timeout']);

        if ('' != $this->config['password']) {
            $this->handler->auth($this->config['password']);
        }

        if (0 != $this->config['select']) {
            $this->handler->select($this->config['select']);
        }
    }

    /**
     * 入队
     * @param string $queue 队列名称
     * @param string $data
     * @return int
     */
    public function set(string $data, string $queue = 'default')
    {
        return $this->handler->rPush($queue, $data);
    }

    /**
     * 出队
     * @param string $queue 队列名称
     * @return string
     */
    public function get(string $queue = 'default')
    {
        return $this->handler->lPop($queue);
    }

    /**
     * 队列当前长度
     * @param string $queue
     * @return int
     */
    public function len(string $queue = 'default')
    {
        return $this->handler->lLen($queue);
    }
}
