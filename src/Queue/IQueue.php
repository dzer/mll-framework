<?php

namespace Mll\Queue;

/**
 * Interface IQueue
 * @package Mll\Queue
 */
interface IQueue
{
    /**
     * 入队
     * @param string $queue 队列名称
     * @param string $data
     * @return mixed
     */
    public function set(string $data, string $queue = 'default');

    /**
     * 出队
     * @param string $queue 队列名称
     * @return mixed
     */
    public function get(string $queue = 'default');

    /**
     * 队列当前长度
     * @param string $queue
     * @return int
     */
    public function len(string $queue = 'default');
}
