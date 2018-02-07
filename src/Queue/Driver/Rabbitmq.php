<?php

namespace Mll\Queue\Driver;

use Mll\Queue\IQueue;

/**
 * Class RabbitMQ
 *
 * @package Mll\Queue\Driver
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Rabbitmq implements IQueue
{
    /** @var  \Redis */
    public $handler;

    /** @var \AMQPConnection */
    protected $amqpConnection;

    /** @var \AMQPChannel */
    protected $amqpChannel;

    /** @var \AMQPExchange */
    protected $amqpExchange;

    /** @var \AMQPQueue */
    protected $amqpQueue;

    protected $config = [
        'host' => 'localhost',
        'port' => '5672',
        'vhost' => '/',
        'login' => 'guest',
        'password' => 'guest',
        'read_timeout' => 0,
        'write_timeout' => 0,
        'connect_timeout' => 0,
        'channel_max' => 256,
        'frame_max' => 131072,
        'heartbeat' => 0,
        'cacert' => null,
        'key' => null,
        'cert' => null,
        'verify' => 1,
        'queue_name' => 'default',
        'exchange_name' => ''
    ];

    /**
     * RabbitMQ constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!extension_loaded('AMQP')) {
            throw new \BadFunctionCallException('not support: AMQP');
        }

        if (!is_object($this->amqpConnection) || !$this->amqpConnection->isConnected()) {
            if (!empty($config)) {
                $this->config = array_merge($this->config, $config);
            }
            $this->init();
        }
    }

    /**
     * init
     *
     * @param string $exchange_name
     * @param string $queue_name
     */
    public function init($exchange_name = '', $queue_name = '')
    {
        if ($exchange_name != '') {
            $this->config['exchange_name'] = $exchange_name;
        }
        if ($queue_name != '') {
            $this->config['queue_name'] = $queue_name;
        }
        if (!is_object($this->amqpConnection) || !$this->amqpConnection->isConnected()) {
            $this->amqpConnection = new \AMQPConnection($this->config);

            //Establish connection AMQP
            $this->amqpConnection->pconnect();
        }

        //Create and declare channel
        $this->amqpChannel = new \AMQPChannel($this->amqpConnection);

        //AMQP Exchange is the publishing mechanism
        $this->setExchange($this->config['exchange_name']);

        //Declare Queue
        $this->setQueue($this->config['queue_name']);
    }

    /**
     * 设置队列
     *
     * @param $queue_name
     */
    protected function setQueue($queue_name)
    {
        $this->config['queue_name'] = $queue_name;
        $this->amqpQueue = new \AMQPQueue($this->amqpChannel);
        $this->amqpQueue->setName($queue_name);
        $this->amqpQueue->setFlags(AMQP_DURABLE);
        $this->amqpQueue->declareQueue();
    }

    /**
     * 设置exchange
     *
     * @param $exchange_name
     */
    protected function setExchange($exchange_name)
    {
        $this->amqpExchange = new \AMQPExchange($this->amqpChannel);
        if ($exchange_name != '') {
            $this->amqpExchange->setName($exchange_name); // 设置关联的exchange名字
            $this->amqpExchange->setType(AMQP_EX_TYPE_DIRECT);
            $this->amqpExchange->setFlags(AMQP_DURABLE);
            $this->amqpExchange->declareExchange();
        }
    }

    /**
     * 将队列绑定到exchange
     *
     * @param string $exchange_name
     * @param string $routing_key
     * @return boolean
     */
    public function bind(string $exchange_name, string $routing_key)
    {
        return $this->amqpQueue->bind($exchange_name, $routing_key);
    }

    /**
     * 入队
     *
     * @param string $message 消息
     * @param string $routing_key
     * @return int
     */
    public function set(string $message, string $routing_key = 'default')
    {
        return $this->amqpExchange->publish($message, $routing_key);
    }

    /**
     * 出队
     * @param string $queue
     * @param int $autoAck 是否自动清除
     * @return \AMQPEnvelope|string
     */
    public function get(string $queue = '', $autoAck = 1)
    {
        if ($queue != '' && $queue != $this->config['queue_name']) {
            $this->setQueue($queue);
        }

        if ($autoAck) {
            $AMQPEnvelope = $this->amqpQueue->get(AMQP_AUTOACK);
        } else {
            $AMQPEnvelope = $this->amqpQueue->get();
        }
        if (!is_object($AMQPEnvelope)) {
            return $AMQPEnvelope;
        }
        return $AMQPEnvelope->getBody();
    }

    /**
     * 队列当前长度
     * @param string $queue
     * @return int
     */
    public function len(string $queue = 'default')
    {
        return $this->amqpQueue->declareQueue();
    }

    /**
     * 消费消息
     * @param callable $callback 回调函数
     * @param int $autoAck 是否在MQ中清除
     */
    public function consume(callable $callback, $autoAck = 1)
    {
        if ($autoAck) {
            $this->amqpQueue->consume($callback, AMQP_AUTOACK);
        } else {
            $this->amqpQueue->consume($callback);
        }
    }

    /**
     * 告知MQ如何处理消息
     * 在消费未开启 autoAck 时，需要调用此方法。
     * @param $delivery_tag
     * @param int $type 1表示消费成功可以删除消息 2表示消费失败，重新放回队列  3表示消费失败，放弃处理
     * @return bool
     * @throws \AMQPQueueException
     */
    public function acknowledge($delivery_tag, $type = 1)
    {
        if ($type == 1) {
            //确认消息
            return $this->amqpQueue->ack($delivery_tag);
        } elseif ($type == 2) {
            //恢复消息
            return $this->amqpQueue->nack($delivery_tag);
        } elseif ($type == 3) {
            //确认取消
            return $this->amqpQueue->reject($delivery_tag);
        } else {
            throw new \AMQPQueueException('Undefined Acknowledge type');
        }
    }
}
