<?php
namespace Mll\Common;

/**
 * AMQP
 *
 * @package Mll\Common
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Amqp
{
    private $conn_args;
    /**
     *
     * @var AMQPConnection
     */
    private static $conn;

    /**
     *
     * @param array $conn_args
     * @see AMQPConnection::__construct()
     */
    function __construct($conn_args)
    {
        $this->conn_args = $conn_args;
        if (self::$conn === null) {
            $conn = new \AMQPConnection($this->conn_args);
            if (!$conn->connect()) {
                throw new \Exception("Cannot connect to the broker  <br> ");
            }
            self::$conn = $conn;
        }


    }

    /**
     *
     * @param string $exchange_name
     * @param string $qname
     * @param string $messages
     */
    public function sendMessage($messages, $qname, $exchange_name, $routing_key = '')
    {

        $conn = self::$conn;
        if (!$conn->isConnected()) {
            throw new \Exception('Connection lost.<br>');
        }
        // 创建channel
        $channel = new \AMQPChannel($conn);
        // 创建exchange
        $ex = new \AMQPExchange($channel);
        $ex->setName($exchange_name); // 设置关联的exchange名字
        $ex->setType(AMQP_EX_TYPE_DIRECT);
        $ex->setFlags(AMQP_DURABLE);
        $ex->declareExchange();


        /*
         * 创建队列
         */
        $q = new \AMQPQueue($channel);
        // 设置队列名字 如果不存在则添加
        $q->setName($qname);
        $q->setFlags(AMQP_DURABLE);
        $q->declareQueue();

        // 将你的队列绑定到routingKey
        $bind_res = $q->bind($exchange_name, $routing_key);
        $channel->startTransaction();
        // 你的消息
        $message = json_encode($messages);
        $publish_res = $ex->publish($message, $routing_key);
        if ($publish_res) {
            $res = $channel->commitTransaction();
        } else {
            $res = $channel->rollbackTransaction();
        }
        // TODO add error handle
        return $res;
    }

    /**
     * 获取消息
     * TODO 优化各种判断
     *
     * @param string $qname
     * @return mixed
     */
    public function getMessage($qname)
    {
        static $q;

        if ($q === null) {
            $conn = self::$conn;
            if (!$conn->isConnected()) {
                throw new \Exception('Connection lost.<br>');
            }
            $channel = new \AMQPChannel($conn);
            $q = new \AMQPQueue($channel);
            $q->setName($qname);
            $q->setFlags(AMQP_DURABLE);
        }

        $messages = $q->get(AMQP_AUTOACK);
        if ($messages) {
            return $messages->getBody();
        }
    }

    public function countMessage($qname, $exchange_name)
    {
        if (!self::$conn->isConnected()) {
            self::$conn = new \AMQPConnection($this->conn_args);
        }
        // 创建channel
        $channel = new \AMQPChannel(self::$conn);
        // 创建exchange
        $ex = new \AMQPExchange($channel);
        $ex->setName($exchange_name); // 设置关联的exchange名字
        $ex->setType(AMQP_EX_TYPE_DIRECT);
        $ex->setFlags(AMQP_DURABLE);

        if (method_exists($ex, 'declareExchange')) {
            $ex->declareExchange();
        } else {
            $ex->declare();
        }

        /*
         * 创建队列
         */
        $q = new \AMQPQueue($channel);
        $q->setName($qname);
        $q->setFlags(AMQP_PASSIVE);
        if (method_exists($q, 'declareQueue')) {
            $msgNum = $q->declareQueue();
        } else {
            $msgNum = $q->declare();
        }

        return $msgNum;
    }

    function __destruct()
    {
        self::$conn->disconnect();
    }
}